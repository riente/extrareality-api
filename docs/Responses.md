# ExtraReality APIs Responses and Errors

This page describes what your endpoints should answer, and what we do with each kind of answer.
It applies to every API we describe here: [Events API](EventsAPIv1.md), [Games API](GamesAPIv1.md)
and the lead endpoints of the [Form Object](FormObject.md).

## Status codes

| Situation                                         | Status                | Body                                        |
|---------------------------------------------------|-----------------------|---------------------------------------------|
| Everything worked                                 | `200`                 | The payload, or `{"success": true}`         |
| A registration was refused because of what the user typed | `422`           | `{"success": false, "message": "..."}`      |
| The signature is missing or wrong                 | `403`                 | `{"success": false, "message": "Invalid signature"}` |
| We sent something malformed                       | `400`                 | `{"success": false, "message": "..."}`      |
| No such event or game                             | `404`                 | `{"success": false, "message": "..."}`      |
| We are calling you too often                      | `429` + `Retry-After` | `{"success": false, "message": "..."}`      |
| Something broke on your side                      | `500`, `502`, `503`   | Anything, we will retry                     |

The second row is the one people get wrong most often. A registration refused because the phone
number is invalid is a normal business outcome, not a broken request — `422` says exactly that,
while `400` would claim we sent you nonsense and `200` would claim it worked.

## The response body

For endpoints that accept leads:

```json
{"success": true}
```

```json
{"success": true, "payUrl": "https://someurl.com/pay/123"}
```

```json
{"success": false, "message": "Your phone is incorrect"}
```

`message` is shown to the person who filled in the form, so write it for them rather than for a
developer. "Your phone is incorrect" is useful; "ERR_VALIDATION_4012" is not.

## What we do with each answer

| You answer                       | We do                                                              |
|----------------------------------|---------------------------------------------------------------------|
| `2xx`                            | Take the payload and carry on                                       |
| `400`, `403`, `404`              | Stop and raise an alert with us. We do not retry — retrying a request you rejected on purpose only repeats the rejection |
| `422`                            | Show `message` to the user. No retry                                |
| `429`                            | Back off and come back after `Retry-After`                          |
| `5xx`, timeouts, connection errors | Retry with an increasing delay, then alert us if it keeps failing |

One exception worth stating plainly: **we do not automatically retry a registration** that may
already have been processed on your side, because we cannot tell a lost response from a lost
request, and a blind retry would sign the same team up twice.

The way out of that is
[`idempotency_key`](FormObject.md#sending-the-same-registration-twice): once your endpoint stores it
and declares `idempotent: true`, a repeated registration cannot create a second one, and we do retry
those.

## Why it matters

It is tempting to answer `200` to everything and put the real outcome in the body. Please do not:
that hides failures from everything built to notice them.

* **Your own monitoring goes blind.** Uptime checks, error-rate dashboards and alerting all key on
  status codes. An endpoint that answers `200` while returning `{"success": false}` to every single
  request looks perfectly healthy on every graph you own.
* **Nobody can retry correctly.** `5xx` means "try again", `4xx` means "do not bother". Collapsed
  into `200`, we cannot tell a broken deploy from a deliberate refusal, so we either retry things
  you rejected or give up on things that would have worked a second later.
* **Caches and proxies store errors.** A `200` is cacheable by default. Return an error as `200`
  and a CDN in front of you may keep serving that error long after you fixed it.
* **Load balancers keep sending traffic to broken instances.** Health checks look at status codes
  too, so an instance that fails every request while answering `200` never gets taken out.

In particular, if your endpoint is genuinely broken, let it fail with a `5xx` rather than dressing
the failure up as a success. A crash that looks like a crash gets fixed; a crash that answers `200`
gets discovered weeks later, when someone asks why the registrations stopped.

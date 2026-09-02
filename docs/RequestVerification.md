# ExtraReality APIs Request Verification

This page describes how to make sure that a request to your endpoints really comes from us.
It applies to every API we describe here: [Events API](EventsAPIv1.md), [Games API](GamesAPIv1.md)
and the lead endpoints of the [Form Object](FormObject.md).

## What we send

Every request carries these headers:

| Header              | Example                          | Description                                    |
|---------------------|----------------------------------|------------------------------------------------|
| `X-Source`          | `extrareality`                   | Who is calling. Usually "extrareality", but there may be other sources |
| `X-Timestamp`       | `2025-05-10T17:00:00+00:00`      | When the request was created, ISO 8601 in UTC  |
| `X-Signature-256`   | `9f86d081...`                    | HMAC-SHA256 signature, hex encoded             |
| `X-Signature`       | `5d41402a...`                    | **Deprecated** legacy MD5 signature            |

For backward compatibility the same values are also duplicated as `source`, `datetime` and
`signature` request parameters (in the query string for GET, in the body for POST), where `datetime`
uses the legacy `"Y-m-d H:i:s"` format in UTC. New integrations should read the headers and ignore
these parameters.

**We strongly advise you to verify the signature before processing the request**, so that you never
hand out your data — including the personal data of the people who registered — to a stranger who
simply guessed the URL.

On one endpoint this is not advice but a requirement: the
[participant contacts](EventsAPIv1.md#participant-contacts) endpoint returns email addresses, so it
must refuse every request whose signature does not check out.

## Recommended: HMAC-SHA256

`X-Signature-256` is an HMAC-SHA256 of a canonical string, keyed with the secret that only you and
we know. The canonical string is these five parts joined with a line feed (`\n`):

```
X-Source header
X-Timestamp header
HTTP method, uppercase, e.g. GET or POST
request target — path and query string, exactly as it arrived, e.g. /api/events?city=1
SHA-256 of the raw request body, hex encoded (of an empty string for GET)
```

In PHP:

```php
$canonical = implode("\n", [
    $source,
    $timestamp,
    $method,
    $requestTarget,
    hash('sha256', $rawBody),
]);

$signature = hash_hmac('sha256', $canonical, $secret);
```

This scheme improves on the legacy one in three ways, and each of them matters:

* **The body is signed.** With the legacy MD5 the signature does not depend on what we send, so
  anyone who once saw a valid signature could reuse it to push arbitrary registration data to your
  lead endpoint. Here, changing a single byte of the body invalidates the signature.
* **HMAC instead of a plain hash.** `md5($source . $datetime . $secret)` is a naive
  secret-suffix construction over a weak, collision-broken hash. HMAC is the standard, well-analysed
  way to key a hash.
* **The request is bound to its target.** A signature captured on the public events list cannot be
  replayed against your registration endpoint.

### Verifying it

```php
function verifyExtrarealityRequest(string $secret, int $maxAgeSeconds = 300): bool
{
    $source    = $_SERVER['HTTP_X_SOURCE'] ?? '';
    $timestamp = $_SERVER['HTTP_X_TIMESTAMP'] ?? '';
    $received  = $_SERVER['HTTP_X_SIGNATURE_256'] ?? '';

    // Reject stale requests, otherwise a captured signature is valid forever
    $ts = strtotime($timestamp);
    if ($ts === false || abs(time() - $ts) > $maxAgeSeconds) {
        return false;
    }

    $canonical = implode("\n", [
        $source,
        $timestamp,
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
        hash('sha256', file_get_contents('php://input')),
    ]);

    $expected = hash_hmac('sha256', $canonical, $secret);

    // hash_equals, not "==", to avoid leaking the answer through timing
    return hash_equals($expected, $received);
}
```

Three details that are easy to get wrong:

* **Compare with `hash_equals()`**, never with `==` or `===`. A plain comparison returns as soon as
  it meets a differing byte, and that timing difference is enough to recover a valid signature
  byte by byte.
* **Hash the raw body**, before any parsing. If your framework has already decoded the JSON, do not
  re-encode it — key order and escaping will differ and the signature will not match. In Laravel
  use `$request->getContent()`, in Symfony `$request->getContent()`, in plain PHP `php://input`.
* **`REQUEST_URI` must be the target as it arrived.** If a proxy or a rewrite rule in front of your
  app changes the path, sign the value your app actually receives and tell us, so we can agree on it.

### The time window

We recommend rejecting anything older than **5 minutes**. Without such a window a signature that
leaked once — through a log file, a proxy, an error report — stays valid forever, and the whole
signing scheme buys you nothing against replay.

If your clock drifts, this check starts failing on valid requests. Keep NTP running.

## Deprecated: legacy MD5

The old scheme is still sent so that existing integrations do not break:

```php
md5($source . $datetime . $secret)
```

where `datetime` is in `"Y-m-d H:i:s"` format (i.e., yyyy-mm-dd hh:mm:ss), in UTC.

It does not authenticate the body and cannot be replay-protected in any meaningful way. Please
migrate to `X-Signature-256`. Once you have, tell us and we will stop sending the legacy header and
parameters to your endpoints — until then both are sent side by side, and you may safely ignore the
one you do not use.

## About the secret

* We can generate a random secret or agree on it with you beforehand. Ask for at least 32 random
  characters — the strength of everything above rests on it.
* It is a shared secret, not a public identifier. Keep it out of your repository, your front end and
  your logs.
* If you suspect it has leaked, tell us and we will rotate it.

## HTTP status codes

When you refuse a request because its signature does not check out, answer `403` with:

```json
{"success": false, "message": "Invalid signature"}
```

A rejected signature is not a user-level problem the person filling in a form can fix, so it should
not look like one. `403` also keeps it out of your success metrics, which is exactly where you want
it if someone starts probing your endpoints.

Older integrations answer `200` to everything, that one included, and we still understand it. See
[Responses and errors](Responses.md) for the full table of status codes and what we do with each.

# ExtraReality Game Events API v1

## Request verification

We sign every request we send you, so that you can make sure it really comes from us and never hand
your data over to a stranger who guessed the URL.

Each request carries the `X-Source`, `X-Timestamp` and `X-Signature-256` headers, where the last one
is an HMAC-SHA256 of the request keyed with a secret that only you and we know.

**Please see [Request verification](RequestVerification.md) for the exact scheme, a ready-to-use PHP
verification snippet and the pitfalls to avoid.**

For what to answer, and which status code to answer it with, see [Responses and errors](Responses.md).

## Quick links

You'll have to develop several endpoints:

* [Events List](#events-list)
* [Single Event](#single-event)
* [Single Game Data](#single-game)
* [Registration](#sign-up-for-an-event)
* [Participant contacts](#participant-contacts) — only if you want us to email your participants

Please also read [Dates and times](#dates-and-times) and [Event status](#event-status) before you start.

## Dates and times

This applies to every date and time in this API: the event `time`, `registration.form.openTime`
and any other timestamp you return to us.

Use [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601) with an explicit UTC offset:

```
"2025-05-10T19:00:00+02:00"
```

Always include the offset. We show your events on sites and apps whose users are not necessarily in
your city, and a bare `"2025-05-10 19:00:00"` gives us no way to tell "19:00 in Warsaw" from "19:00
in Minsk" — the person reading your listing would see the wrong time in their own calendar.

Do not forget DST: if you build the string by hand, `"+02:00"` in July and `"+01:00"` in December
are different offsets of the same zone. Letting your date library format the value avoids that.

## Event status

Plans change: an event gets cancelled, or every seat is taken a week before it starts. The optional
`status` property tells us which of these happened.

| Value       | Meaning                                                                  |
|-------------|--------------------------------------------------------------------------|
| `active`    | The default. The event takes place and accepts registrations as usual    |
| `soldout`   | The event takes place, but there are no seats left                       |
| `cancelled` | The event will not take place                                            |

If you omit the property entirely, we treat the event as `active` — so existing integrations that
know nothing about statuses keep behaving exactly as before.

You can add a `statusComment`: a short human-readable note that we show next to the event, up to 255
characters, e.g. `"Перенесено на 24 мая"` or `"Sold out, join the waiting list"`.

```json
{
    "id": 1,
    "status": "cancelled",
    "statusComment": "The host got ill, sorry!"
}
```

### Please do not just remove the event

The tempting alternative is to drop a cancelled event from the list. Please do not, at least until
its `time` has passed.

An event that vanishes from your feed is indistinguishable from a bad deploy, an expired certificate
or a timeout on your side — we cannot tell "this event is cancelled" from "this endpoint is broken
right now". A vanished event also leaves the people who already registered with no explanation
anywhere, while `cancelled` plus a `statusComment` reaches every place your event was shown.

### What we do with each status

* `active` — the event is listed and the registration form, if any, is shown.
* `soldout` — the event stays visible, but we hide the registration form and stop sending you leads
  for it. Use it instead of removing the `registration` object, so the event does not look as if it
  never accepted registrations at all.
* `cancelled` — the event is shown as cancelled and we send you no leads for it. We keep displaying
  it until `time` passes, then drop it.

Because `cancelled` and `soldout` live in a single field, one rule settles the overlap: **a cancelled
event is `cancelled` even if it was sold out first.** The event not happening is what the user needs
to know.

## Events List

It is presumed that all the events on the list are held in one city (or online).

You can create several endpoints for each city if you need, or make it accept some parameters.
For example:

```
https://your-site.com/api/events/city1
https://your-site.com/api/events?city=1
https://your-site.com/api/events?city=2
```

Array of objects, each one containing the event data.

```json
[
    {
        "id": 1,
        "type": "online",
        "status": "active",
        "game": {
            "id": 1,
            "brand": "Connectit",
            "name": "No3. Hunting"
        },
        "gameUrl": "https://your-site.com/api/games/1",
        "location": "Your home",
        "time": "2025-05-10T19:00:00+02:00",
        "price": { "amount": 10, "currency": "EUR", "per": "player" },
        "url": "https://your-site.com/api/events/1"
    },
    {
        "id": 2,
        "type": "offline",
        "status": "soldout",
        "statusComment": "Sold out, join the waiting list",
        "game": {
            "id": 1,
            "brand": "Connectit",
            "name": "No3. Hunting",
            "img": "https://detectit.org/img/pic1.jpg",
            "description": "Someone committed a crime"
        },
        "location": "Cool Cafe",
        "time": "2025-05-17T19:00:00+02:00",
        "price": { "amount": 60, "currency": "EUR", "per": "team" },
        "url": "https://your-site.com/api/events/2"
    }
]
```

| Field        | Required | Description                                                         |
|--------------|----------|---------------------------------------------------------------------|
| **id**       | true     | Unique event ID from your system                                    |
| **type**     | true     | Available values: "online", "offline"                               |
| status       | false    | "active" (default), "soldout" or "cancelled", see [Event status](#event-status) |
| statusComment| false    | Short note shown next to the event, up to 255 characters            |
| **game**     | true     | [Object with game data](#single-game), see [Referring to a game](#referring-to-a-game) |
| gameUrl      | false    | URL to fetch the full [game data](GamesAPIv1.md#single-game)        |
| **location** | true     | String, where the event takes place                                 |
| **time**     | true     | When the event is held, see [Dates and times](#dates-and-times)      |
| **price**    | true     | Object, better described in [Single Event](#properties-description) |
| **url**      | true     | URL to [Single Event](#single-event)                                |

## Single Event

```json
{
    "id": 1,
    "type": "online",
    "status": "active",
    "statusComment": null,
    "game": {
        "id": 1,
        "brand": "Connectit",
        "name": "No3. Hunting"
    },
    "gameUrl": "https://your-site.com/api/games/1",
    "location": "Your home",
    "coordinates": { "lat": 52.2297, "long": 21.0122 },
    "time": "2025-05-10T19:00:00+02:00",
    "price": { "amount": 10, "currency": "BYN", "per": "player" },
    "registration": {
        "contactsUrl": "https://your-site.com/api/events/1/contacts",
        "teams": [
            { "id": 123, "name": "Vasilisy", "status": "confirmed", "players": 6 },
            { "id": 124, "name": "Cats", "status": "reserve", "players": 7 }
        ],
        "form": {
            "openTime": "2025-05-05T12:00:00+02:00",
            "endpoint": {
                "url": "https://your-site.com/reg/1",
                "method": "POST",
                "format": "json",
                "idempotent": true
            },
            "maxTeams": 20,
            "maxPlayers": 10,
            "fields": [
                { "type": "text", "name": "team_name", "required": true, "title": "Team Name", "description": null, "max": 20 },
                { "type": "textarea", "name": "comment", "required": false, "title": "Comment", "description": null, "max": 200 },
                { "type": "number", "name": "players_num", "required": true, "title": "Players", "description": null, "max": 10 },
                { "type": "phone", "name": "phone", "required": true, "title": "Your phone", "description": null },
                { "type": "email", "name": "email", "required": true, "title": "Your email", "description": "We'll send links" },
                { "type": "radio", "name": "is_first_time", "required": true, "title": "Are you noob?", "variants": [
                    { "value": 0, "title": "No" },
                    { "value": 1, "title": "Yes" }
                ] },
                { "type": "select", "name": "language", "required": true, "title": "Game language", "variants": [
                    { "value": "en", "title": "English" },
                    { "value": "pl", "title": "Polski" }
                ] },
                { "type": "checkbox", "name": "agree", "required": true, "title": "Do you agree?", "value": 1 },
                { "type": "checkboxes", "name": "source", "required": false, "title": "How do you know us?", "variants": [
                    { "value": "radio", "title": "Radio" },
                    { "value": "web", "title": "Internet" },
                    { "value": "other", "title": "Other" }
                ] },
                { "type": "hidden", "name": "event_id", "value": 123 }
            ]
        }
    }
}
```

### Properties description

| Field                             | Required | Default | Description                                                    |
|-----------------------------------|----------|---------|----------------------------------------------------------------|
| **id**                            | **true** |         | Unique ID from your system                                     |
| **type**                          | true     |         | Available values: "online", "offline"                          |
| status                            | false    | active  | "active", "soldout" or "cancelled"                             |
|                                   |          |         | See [Event status](#event-status)                              |
| statusComment                     | false    | null    | Short note shown next to the event, up to 255 characters       |
| **game**                          | true     |         | [Object with game data](#single-game), see below               |
| gameUrl                           | false    | null    | URL to fetch the full [game data](GamesAPIv1.md#single-game)   |
| **location**                      | true     |         | String, where the event takes place                            |
| coordinates                       | false    | null    | Null or object with required "lat" and "long" properties       |
| coordinates.lat                   | false    |         | Float value, latitude                                          |
| coordinates.long                  | false    |         | Float value, longitude                                         |
| **time**                          | true     |         | See [Dates and times](#dates-and-times)                        |
| **price**                         | true     |         | Object                                                         |
| **price.amount**                  | true     |         | Float or integer value                                         |
| **price.currency**                | true     |         | [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)             |
| **price.per**                     | true     |         | Available values: "player", "team"                             |
| registration                      | false    | null    | Null or object                                                 |
| registration.form.maxTeams        | false    |         | Max number of teams                                            |
| registration.form.maxPlayers      | false    |         | Max number of people the location can have                     |
| registration.contactsUrl          | false    | null    | URL we call to fetch the participants' email addresses         |
|                                   |          |         | See [Participant contacts](#participant-contacts)              |
| registration.teams                | false    |         | Described below                                                |
| registration.form                 | false    |         | You can provide it if you want us to send you leads            |
|                                   |          |         | Thoroughly described in [Form Object](FormObject.md)           |
| registration.form.fields          | false    |         | Array of objects, described [here](FormObject.md#field-object) |

### `registration.teams[]` description

| Field       | Required | Default | Description                                                        |
|-------------|----------|---------|--------------------------------------------------------------------|
| **id**      | true     |         | Unique ID of the registration in your system                       |
| **name**    | true     |         | Player's or team's name                                            |
| status      | false    | new     | Available values: "new", "confirmed", "reserve", "cancelled"       |
| **players** | true     |         | Number of players on the team                                      |

There is deliberately no email address in this object. This endpoint describes an event, we may
request it often, and its URL is easy to guess — an address here would be one guessed URL away from
a stranger. Addresses travel through
[`registration.contactsUrl`](#participant-contacts) instead.

### `registration.form.fields` description

It is an array of objects, each describing a single form field that we'll use to build the request to your endpoint.

Please see [FormObject](FormObject.md#field-object) for details.

## Participant contacts

Sometimes we need the email addresses of the people who registered — to send them the link to an
online game, the venue address, or a reminder. Those addresses are personal data, and this section
is about handing them over without leaving them lying around.

**If you would rather not share addresses at all, simply leave `contactsUrl` out and email your
participants yourself.** Nothing else in this API needs them.

### Why not just put them in the event

The [Single Event](#single-event) response describes an event so that it can be rendered in a
listing. It is fetched often, it may be cached, and its URL is easy to guess — `/api/events/1`,
`/api/events/2`, and so on. An `email` field in there turns that endpoint into a downloadable list
of your customers' addresses for anyone who tries.

Contacts therefore live behind their own URL, which we call only at the moment we actually have
something to send.

### The endpoint

Put its address in `registration.contactsUrl`. We send a signed `GET` request to it, exactly as
described in [Request verification](RequestVerification.md), and expect:

```json
{
    "eventId": 1,
    "contacts": [
        { "registrationId": 123, "email": "some@mail.com", "consentAt": "2025-05-05T12:30:00+02:00" },
        { "registrationId": 124, "email": "cats@mail.com", "consentAt": "2025-05-06T09:15:00+02:00" }
    ]
}
```

| Field                       | Required | Description                                                        |
|-----------------------------|----------|---------------------------------------------------------------------|
| **eventId**                 | true     | The event these contacts belong to                                  |
| **contacts**                | true     | Array, may be empty                                                 |
| **contacts.registrationId** | true     | The `id` of the matching [team](#registrationteams-description)      |
| **contacts.email**          | true     | The address to write to                                             |
| consentAt                   | false    | When the person agreed to receive emails, see [Dates and times](#dates-and-times) |

Note what is *not* in there: no names, no phone numbers, no team composition. You already send the
names in `registration.teams[]`, and we match the two by `registrationId`. Sending a field twice
only doubles the number of places it can leak from.

### Rules for this endpoint

Everywhere else in this API verifying our signature is advice. **Here it is a requirement.**

* **Verify `X-Signature-256` and refuse anything that fails.** This is the one endpoint where a
  missed check hands over personal data rather than a schedule.
* **Reject stale requests.** Five minutes, as described in
  [Request verification](RequestVerification.md#the-time-window). Without it, one captured signature
  is a permanent key to your participant list.
* **Serve it over HTTPS only**, and answer plain HTTP with a redirect to it rather than the data.
* **Send `Cache-Control: no-store`**, so that no proxy or CDN on the way keeps a copy.
* **Never accept or return an address in the query string.** URLs end up in access logs, browser
  history and `Referer` headers. Keep contacts in the response body.
* **Return only the people we still need to write to** — skip registrations you have already
  cancelled, and skip anyone who did not agree to be emailed.
* **Keep the response out of your logs.** Logging the full body of this one is the easiest way to
  end up with a plain-text copy of every address you have.

If the signature does not check out, do not return an empty list as if the event had no
participants — that reads as a data problem and we will retry. Answer instead:

```json
{"success": false, "message": "Invalid signature"}
```

with HTTP status code `403`, as described in [Responses and errors](Responses.md).

### Consent, and what we do with the addresses

You are handing us personal data, so a short and honest summary of both sides:

* **Ask for consent at registration.** A `checkbox` field in your
  [form](FormObject.md#field-object) is enough, as long as it names what the person is agreeing to.
  If you cannot show that someone agreed, do not put their address in the response.
* **We use the addresses only to email the participants about that event** — links, reminders,
  changes. Not for marketing, and not for anything else.
* **We do not pass them on** to other partners, and we do not add them to any list of ours.
* **We delete them once they are no longer needed** for the event they were collected for.
* **Deletion requests reach us through you.** If a participant asks you to erase their data, tell
  us and we will remove our copy too.

## Games List

You can create endpoints to list all your games that are available. Please see [GamesAPIv1](GamesAPIv1.md#games-list) for details.

## Single Game

### Referring to a game

Every event must carry a `game` **object** with at least the fields listed below. If you also want to
expose the full game data, add a `gameUrl` pointing to your
[Single Game](GamesAPIv1.md#single-game) endpoint.

```json
{
    "game": { "id": 1, "brand": "Connectit", "name": "No3. Hunting" },
    "gameUrl": "https://your-site.com/api/games/1"
}
```

The reason we ask for the object even when a URL is available: we render lists of dozens of events,
and each event whose game is only a URL costs us one more HTTP request before we can draw a single
line of your listing. With the object inlined we can show the event immediately and fetch the full
game data only when a user opens it.

Keep the inlined object small — `id`, `brand`, `name` and, if you have one, `img`. The long
description, the gallery and the prices belong behind `gameUrl`.

### Fields of the game object

It is very thoroughly described in [Games API](GamesAPIv1.md#single-game), and here we need at least
the following:

| Field       | Required | Description                                                                                     |
|-------------|----------|-------------------------------------------------------------------------------------------------|
| **id**      | true     | Unique game ID from your system                                                                 |
| **brand**   | true     | If you have several categories of games you can indicate one here, or it can always be the same |
| **name**    | true     | Game name                                                                                       |
| img         | false    | If you have unique game posters/pics                                                            |
| description | false    | Up to 2048 characters                                                                           |

## Sign up for an event

We will send the data in accordance with `registration.form.fields` of your event to the URL provided by you in `registration.form.endpoint.url`.

The only field we add to your own is `idempotency_key`, which is what makes a repeated registration
safe — see [Sending the same registration twice](FormObject.md#sending-the-same-registration-twice).
Everything needed to verify that the request is ours travels in headers, see
[Request verification](RequestVerification.md).

In case of successful processing, you return the following JSON response:

```json
{"success": true, "registrationId": 123}
```

`registrationId` is the id of the registration you just created — the same one you return in
[`registration.teams[].id`](#registrationteams-description). Without it we cannot match the lead we
sent you to the team in your event, and we cannot recognise the answer to a repeated request.

If you need the user to pay online for this event, you can also provide a "payUrl" with the response:

```json
{"success": true, "registrationId": 123, "payUrl": "https://someurl.com/pay/123"}
```

We send the user to that URL. We do not hear back from the payment provider, so **you tell us the
payment went through by flipping that registration's `status` to `confirmed`** the next time we read
the event. If you never change it, the registration stays `new` for us forever.

In case of error:

```json
{"success":false, "message": "Your phone is incorrect"}
```

The `message` is shown to the person who filled in the form, so write it for them.

Answer such a refusal with `422`. Keep the other status codes for the situations they describe:
`403` if our signature does not check out, `5xx` if something broke on your side. The full table, and
what we do with each answer, is in [Responses and errors](Responses.md).

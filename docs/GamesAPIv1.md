# ExtraReality Games API v1

You can implement this API to provide us the information about your games.

If you hold repetitive scheduled events, think of using the [Events API](EventsAPIv1.md).

According to this API, you'll have to develop several endpoints:

* [Games List](#games-list)
* [Single Game Data](#single-game)
* Endpoint for accepting leads (described on [Form Object](FormObject.md) page)

## Request verification

We sign every request we send you, so that you can make sure it really comes from us and never hand
your data over to a stranger who guessed the URL.

Each request carries the `X-Source`, `X-Timestamp` and `X-Signature-256` headers, where the last one
is an HMAC-SHA256 of the request keyed with a secret that only you and we know.

**Please see [Request verification](RequestVerification.md) for the exact scheme, a ready-to-use PHP
verification snippet and the pitfalls to avoid.**

For what to answer, and which status code to answer it with, see [Responses and errors](Responses.md).

## Games List

It is presumed that all the games on the list are available in one city (or everywhere, like online or with delivery).

You can create several endpoints for each city if you need, or make it accept some parameters.
For example:

```
https://your-site.com/api/games/city1
https://your-site.com/api/games?city=1
https://your-site.com/api/games?city=2
```

The endpoint must return an array of objects, each one containing the game data.

```json
[
    {
        "id": 1,
        "brand": "Detective Games",
        "name": "Your Game",
        "url": "https://your-site.com/api/games/1",
        "img": "https://your-site.com/img/pic1.jpg",
        "description": "Some description"
    },
    {
        "id": 2,
        "brand": "Detective Games",
        "name": "Second Game",
        "url": "https://your-site.com/api/games/2",
        "img": "https://your-site.com/img/pic2.jpg",
        "description": "Some description"
    }
]
```

| Field       | Required | Description                                                                                 |
|-------------|----------|---------------------------------------------------------------------------------------------|
| **id**      | true     | Unique game ID from your system                                                             |
| **brand**   | true     | If you have several categories of games, you can indicate one, or it can always be the same |
| **name**    | true     | Game name                                                                                   |
| **url**     | true     | URL to [Single Game](#single-game) object                                                   |
| img         | false    | If you have unique game posters/pics                                                        |
| description | false    | Up to 2048 characters                                                                       |

## Single Game

```json
{
    "id": 57,
    "brand": "Detective Games",
    "name": "No3. The Hunt",
    "type": "offline",
    "locale": "en",
    "img": "https://your-site.com/img/pic1.jpg",
    "description": "Someone committed a crime",
    "prices": [
        { "amount": 10, "currency": "EUR", "description": "Package #1" },
        { "amount": 20, "currency": "EUR", "description": "Package #2" }
    ],
    "gallery": [
        "https://your-site.com/img/pic1.jpg",
        "https://your-site.com/img/pic2.jpg",
        "https://your-site.com/img/pic3.jpg",
        "https://your-site.com/img/pic4.jpg"
    ],
    "form": {
        "openTime": "2025-05-05T12:00:00+02:00",
        "endpoint": {
            "url": "https://your-site.com/book/1",
            "method": "POST",
            "format": "json",
            "idempotent": true
        },
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
            { "type": "hidden", "name": "game_id", "value": 57 }
        ]
    }
}
```

| Field                 | Required | Default | Description                                                           |
|-----------------------|----------|---------|-----------------------------------------------------------------------|
| **id**                | true     |         | Unique ID of the game in your system                                  |
| **brand**             | true     |         | If you have several categories of games, or it can always be the same |
| **name**              | true     |         | Game's name, e.g. "Movies #3"                                         |
| **type**              | true     |         | Available values: "online", "offline"                                 |
| locale                | false    | depends | Language of the game                                                  |
| type                  | false    | offline | Available values: online, offline                                     |
| prices                | false    | []      | Array of Price objects (described below)                              |
| **prices[].amount**   | true     |         | Float or integer value                                                |
| **prices[].currency** | true     |         | [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)                    |
| prices[].description  | false    |         | If you have one and only price, you can omit the description          |
| img                   | false    | null    | If you have unique game posters/pics                                  |
| description           | false    | null    | Up to 2048 characters                                                 |
| gallery               | false    |         | Array of string URLs                                                  |
| form                  | false    | null    | Optional. Described thoroughly in [FormObject](FormObject.md)         |

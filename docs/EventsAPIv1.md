# ExtraReality Game Events API v1

We send the following parameters with each request:

* datetime (just the timestamp of current request, for example, "2019-05-01 12:00:00")
* source
* signature

Signature is formed based on a secret key that only you and we know. By verifying this signature, you can make sure that the request is truly from us, although this check is not required. Anyway, it's always better to perform this check so that you don't occasionally output the information to unauthorized users.

The signature is formed in the following manner:

```php
md5($source . $datetime . $secret)
```

Used parameters:

* source — usually "extrareality" but there may be other sources as well
* datetime — in this format "Y-m-d H:i:s" (i.e., yyyy-mm-dd hh:mm:ss)
* secret — we can generate a random one or decide on it with you beforehand

You should always respond with HTTP status code 200 to any request.

**We strongly advise you to check the request signature before processing the request, so that you are sure that the request comes from us.**

## Quick links

You'll have to develop several endpoints:

* [Events List](#events-list)
* [Single Event](#single-event)
* [Single Game Data](#single-game)
* [Registration](#sign-up-for-an-event)

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

```
[
    {
        id: 1,
        type: online,
        game: "https://your-site.com/api/games/1",
        location: "Your home",
        time: "2025-05-10 19:00:00",
        price: { amount: 10, currency: "EUR", per: "player" },
        url: "https://your-site.com/api/events/1"
    },
    {
        id: 2,
        type: offline,
        game: {
            id: 1,
            brand: "Connectit",
            name: "No3. Hunting",
            img: "https://detectit.org/img/pic1.jpg",
            description: "Someone commited a crime"
        },
        location: "Cool Cafe"
        ...
    }
]
```

| Field        | Required | Description                                                         |
|--------------|----------|---------------------------------------------------------------------|
| **id**       | true     | Unique event ID from your system                                    |
| **type**     | true     | Available values: "online", "offline"                               |
| **game**     | true     | [Object with game data](#single-game) or string URL to get it       |
| **location** | true     | String, where the event takes place                                 |
| **time**     | true     | When the event is held, format is "Y-m-d H:i:s"                     |
| **price**    | true     | Object, better described in [Single Event](#properties-description) |
| **url**      | true     | URL to [Single Event](#single-event)                                |

## Single Event

```
{
    id: 1,
    type: online,
    game: "https://your-site.com/api/games/1",
    location: "Your home",
    coordinates: { lat: "", long: "" },
    time: "2025-05-10 19:00:00",
    price: { amount: 10, currency: "BYN", per: "player" },
    registration: {
        teams: [
             { id: 123, name: "Vasilisy", status: 'confirmed", players: "5-6", email: "some@mail.com" },
             { id: 123, name: "Cats", status: 'reserve", players: 7, email: null }
        ],
        form: {
            openTime: "2025-05-05 12:00:00",
            endpoint: {
                url: "https://your-site.com/reg/1",
                method: "POST",
                format: "json"
            },
            maxTeams: 20,
            maxPlayers: 10,
            fields: [
                { type: "text", name: "team_name", required: true, title: "Team Name", description: null, max: 20 },
                { type: "textarea", name: "comment", required: false, title: "Comment", description: null, max: 200 },
                { type: "number", name: "players_num", required: true, title: "Players", description: null, max: 10 },
                { type: "phone", name: "phone", required: true, title: "Your phone", description: null },
                { type: "email", name: "email", required: true, title: "Your email", description: "We'll send links" },
                { type: "radio", name: "is_first_time", required: true, title: "Are you noob?", variants:  [
                    { value: 0, title: "No" },
                    { value: 1, title: "Yes" },
                ] },
                { type: "select", name: "is_first_time", required: true, title: "Are you noob?", variants:  [
                    { value: "no", title: "No" },
                    { value: "yes", title: "Yes" },
                ] },
                { type: "checkbox", name: "agree", required: true, title: "Do you agree?", value: 1 },
                { type: "checkboxes", name: "source", required: false, title: "How do you know us?", variants:  [
                    { value: "radio", title: "Radio" },
                    { value: "web", title: "Internet" },
                    { value: "other", title: "Other" },
                ] },
                { type: "hidden", name: "event_id", value: 123 },
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
| **game**                          | true     |         | [Object with game data](#single-game) or URL to get it         |
| **location**                      | true     |         | String, where the event takes place                            |
| coordinates                       | false    | null    | Null or object with required "lat" and "long" properties       |
| coordinates.lat                   | false    |         | Float value, latitude                                          |
| coordinates.long                  | false    |         | Float value, longitude                                         |
| **time**                          | true     |         | Format is "Y-m-d H:i:s"                                        |
| **price**                         | true     |         | Object                                                         |
| **price.amount**                  | true     |         | Float or integer value                                         |
| **price.currency**                | true     |         | [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)             |
| **price.per**                     | true     |         | Available values: "player", "team"                             |
| registration                      | false    | null    | Null or object                                                 |
| registration.maxTeams             | false    |         | Max number of teams                                            |
| registration.maxPlayersInTeam     | false    |         | Max number of players on the team                              |
| registration.teams                | false    |         | Described below                                                |
| registration.form                 | false    |         | You can provide it if you want us to send you leads            |
|                                   |          |         | Thoroughly described in [Form Object](FormObject.md)           |
| registration.form.fields          | false    |         | Array of objects, described [here](FormObject.md#field-object) |

### `registration.teams[]` description

| Field       | Required | Default | Description                                                        |
|-------------|----------|---------|--------------------------------------------------------------------|
| **id**      | true     |         | Unique ID of the registration in your system                       |
| **name**    | true     |         | Player's or team's name                                            |
| **status**  | true     | new     | Available values: "new", "confirmed", "reserve", "cancel"          |
| **players** | true     |         | Number of players on the team                                      |
| email       | false    |         | Necessary only if you use sending game info emails via our service |

### `registration.form.fields` description

It is an array of objects, each describing a single form field that we'll use to build the request to your endpoint.

Please see [FormObject](FormObject.md#field-object) for details.

## Games List

You can create endpoints to list all your games that are available. Please see [GamesAPIv1](GamesAPIv1.md#games-list) for details.

## Single Game

It is very thoroughly described in [Games API](GamesAPIv1.md#single-game), but if you decide to provide an object instead of
a URL, we need at least the following fields:

| Field       | Required | Description                                                                                     |
|-------------|----------|-------------------------------------------------------------------------------------------------|
| **id**      | true     | Unique game ID from your system                                                                 |
| **brand**   | true     | If you have several categories of games you can indicate one here, or it can always be the same |
| **name**    | true     | Game name                                                                                       |
| img         | false    | If you have unique game posters/pics                                                            |
| description | false    | Up to 2048 characters                                                                           |

## Sign up for an event

We will send the data in accordance with `registration.form.fields` of your event to the URL provided by you in `registration.form.endpoint.url`.

In case of successful processing, you return the following JSON response:

```json
{"success": true}
```

If you need the user to pay online for this event, you can also provide a "payUrl" with the response:

```json
{"success": true, "payUrl": "https://someurl.com/pay/123"}
```

In case of error:

```json
{"success":false, "message": "Your phone is incorrect"}
```

**Important!** Note that even in case of errors you must always return HTTP 200 status code.

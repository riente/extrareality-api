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

## Quick links

You'll have to develop several endpoints:

* [Events List](#events-list)
* [Single Event](#single-event)
* [Single Game Data](#single-game)
* [Registration](#sign-up-for-an-event)

## Events List

Array of objects, each one containing the event data

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

| Field        | Required | Description                                                   |
|--------------|----------|---------------------------------------------------------------|
| **id**       | true     | Unique event ID from your system                              |
| **type**     | true     | Available values: "online", "offline"                         |
| **game**     | true     | [Object with game data](#single-game) or string URL to get it |
| **location** | true     | String, where the event takes place                           |
| **time**     | true     | When the event is held, format is "Y-m-d H:i:s"               |
| **price**    | true     | Object, better described in [Single Event](#single-event)     |
| **url**      | true     | URL to [Single Event](#single-event)                          |

## Single Event

```
{
    id: 1,
    type: online,
    city: null,
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
                mehtod: "POST",
                format: "json"
            },
            maxTeams: 20,
            maxPlayersInTeam: 10,
            fields: [
                { type: "text", name: "team_name", required: true, title: "Team Name", description: null, max: 20 },
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
            ],
        },
    },
}
```

### Properties description

| Field                             | Required | Default | Description                                              |
|-----------------------------------|----------|---------|----------------------------------------------------------|
| **id**                            | **true** |         | Unique ID from your system                               |
| **type**                          | true     |         | Available values: "online", "offline"                    |
| **game**                          | true     |         | [Object with game data](#single-game) or URL to get it   |
| **location**                      | true     |         | String, where the event takes place                      |
| coordinates                       | false    | null    | Null or object with required "lat" and "long" properties |
| coordinates.lat                   | false    |         | Float value, latitude                                    |
| coordinates.long                  | false    |         | Float value, longitude                                   |
| **time**                          | true     |         | Format is "Y-m-d H:i:s"                                  |
| **price**                         | true     |         | Object                                                   |
| **price.amount**                  | true     |         | Float or integer value                                   |
| **price.currency**                | true     |         | [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)       |
| **price.per**                     | true     |         | Available values: "player", "team"                       |
| registration                      | false    | null    | Null or object                                           |
| registration.maxTeams             | false    |         | Max number of teams                                      |
| registration.maxPlayersInTeam     | false    |         | Max number of players on the team                        |
| registration.teams                | false    |         | Described below                                          |
| registration.form                 | false    |         | You can provide it if you want us to send you leads      |
| registration.form.openTime        | false    | now     | When the form becomes available, format is "Y-m-d H:i:s" |
| registration.form.endpoint        | false    |         | Object describing the URL and format of our requests     |
| registration.form.endpoint.url    | false    |         | URL of your site to which we'll send the requests        |
| registration.form.endpoint.method | false    | POST    | HTTP method                                              |
| registration.form.endpoint.format | false    | form    | Available values: "form", "json"                         |
| registration.form.fields          | false    |         | Described below                                          |

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

#### Field object

| Property    | Required | Description                                                                              |
|-------------|----------|------------------------------------------------------------------------------------------|
| type        | true     | Possible values: text, number, phone, email, radio, select, checkbox, checkboxes, hidden |
| name        | true     | Field's "name" attribute, the var name that we'll send to you                            |
| value       | false    | Predefined value for checkboxes or hidden fields                                         |
| required    | true     | Boolean, whether the field is required                                                   |
| title       | true     | The field's title, which the user can see on the UI                                      |
| description | false    | May be provided if you wish to describe the field better                                 |
| max         | false    | Available for "text" (max characters) and "number" (max numeric value)                   |
| variants    | false    | Available for "radio", "select", "checkboxes"                                            |

#### Descriptions by type

| Type       | Description                                                                               |
|------------|-------------------------------------------------------------------------------------------|
| text       | A simple text input                                                                       |
| number     | A numeric input which accepts only numbers                                                |
| phone      | A text input which ensures the value is a valid phone                                     |
| email      | A text input which ensures the value is a valid email                                     |
| radio      | Radiobuttons to let the user pick one of the provided variants                            |
| select     | A select box to let the user pick one of the provided variants                            |
| checkbox   | A single checkbox for yes/no question, can be used to make the user accept some agreement |
| checkboxes | If you want the user to arbitrarily choose an array of possible variants                  |
| hidden     | If you want to send some predefined data with the request, for example some tracking ID   |

## Single Game

```
{
    id: 57,
    brand: "Detective Games",
    name: "No3. The Hunt",
    img: "https://your-site.com/img/pic1.jpg",
    description: "Someone commited a crime"
}
```

| Field       | Required | Default | Description                                                           |
|-------------|----------|---------|-----------------------------------------------------------------------|
| **id**      | true     |         | Unique ID of the game in your system                                  |
| **brand**   | true     |         | If you have several categories of games, or it can always be the same |
| **name**    | true     |         | Game's name, e.g. "Movies #3"                                         |
| img         | false    | null    | If you have unique game/event posters/pics                            |
| description | false    | null    | Up to 2048 characters                                                 |

## Sign up for an event

We will send the data in accordance with `registration.form.fields` of your event to the URL provided by you in `registration.form.endpoint.url`.

In case of successful processing, you return the following JSON response:

```json
{"success": true}
```

In case of error:

```json
{"success":false, "message": "Your phone is incorrect"}
```

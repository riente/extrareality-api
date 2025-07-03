# ExtraReality APIs Form Object Description

In some of our APIs you can provide a `form` object, if you want us to be able to send you leads.

For example, in [Events API](EventsAPIv1.md#single-event) or in [Games API](GamesAPIv1.md#single-game).

## Quick links

* [Single Event Example](#single-event-example)
* [Single Game Example](#single-game-example)
* [Properties Description](#properties-description)
* [Requests to your endpoint](#requests-to-your-endpoint)

## Single Event example

You can find more details [here](EventsAPIv1.md#single-event)

```
{
    id: 1,
    time: "2025-05-10 19:00:00",
    ...
    
    registration: {
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

## Single Game example

You can find more details [here](GamesAPIv1.md#single-game)

```
{
    id: 57,
    name: "No3. The Hunt",
    ...
    
    form: {
        openTime: "2020-01-01 00:00:00",
        endpoint: {
            url: "https://your-site.com/api/registration",
            method: "POST",
            format: "form",
        },
        fields: [
            { type: "text", name: "name", required: true, title: "Your name" },
            
            ...
        ]
    }
}
```

## Properties description

| Field                 | Required | Default | Description                                              |
|-----------------------|----------|---------|----------------------------------------------------------|
| form                  | false    |         | You can provide it if you want us to send leads to you   |
| form.openTime         | false    | now     | When the form becomes available, format is "Y-m-d H:i:s" |
| form.maxTeams         | false    |         | Maximum number of teams allowed to register              |
| form.maxPlayers       | false    |         | Maxumum number of people the location can have           |
| **form.endpoint**     | true     |         | Object describing the URL and format of our requests     |
| **form.endpoint.url** | true     |         | URL of your site to which we'll send the requests        |
| form.endpoint.method  | false    | POST    | HTTP method                                              |
| form.endpoint.format  | false    | form    | Available values: "form", "json"                         |
| **form.fields**       | true     |         | Described below                                          |

### `form.fields` description

It is an array of objects, each describing a single form field that we'll use to build the request to your endpoint.

### Field object

| Property    | Required | Description                                                                                        |
|-------------|----------|----------------------------------------------------------------------------------------------------|
| type        | true     | Possible values: text, textarea, number, phone, email, radio, select, checkbox, checkboxes, hidden |
| name        | true     | Field's "name" attribute, the var name that we'll send to you                                      |
| value       | false    | Predefined value for checkboxes or hidden fields                                                   |
| required    | true     | Boolean, whether the field is required                                                             |
| title       | true     | The field's title, which the user can see on the UI                                                |
| description | false    | May be provided if you wish to describe the field better                                           |
| max         | false    | Available for "text" (max characters) and "number" (max numeric value)                             |
| variants    | false    | Available for "radio", "select", "checkboxes"                                                      |

### Descriptions by type

| Type       | Description                                                                               |
|------------|-------------------------------------------------------------------------------------------|
| text       | A simple text input                                                                       |
| textarea   | A textarea input                                                                          |
| number     | A numeric input which accepts only numbers                                                |
| phone      | A text input which ensures the value is a valid phone                                     |
| email      | A text input which ensures the value is a valid email                                     |
| radio      | Radiobuttons to let the user pick one of the provided variants                            |
| select     | A select box to let the user pick one of the provided variants                            |
| checkbox   | A single checkbox for yes/no question, can be used to make the user accept some agreement |
| checkboxes | If you want the user to arbitrarily choose an array of possible variants                  |
| hidden     | If you want to send some predefined data with the request, for example some tracking ID   |

## Requests to your endpoint

We will send the data in accordance with `form.fields` of your object to the URL provided by you in `form.endpoint.url`.

In case of successful processing, you return the following JSON response:

```json
{"success": true}
```

If you need the user to pay online for something, you can also provide a "payUrl" with the response:

```json
{"success": true, "payUrl": "https://someurl.com/pay/123"}
```

In case of error:

```json
{"success":false, "message": "Your phone is incorrect"}
```

**Important!** Note that even in case of errors you must always return HTTP 200 status code.

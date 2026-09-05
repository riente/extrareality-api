> **Languages:** English | [Русский](ru/FormObject.md)

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

```json
{
    "id": 1,
    "type": "online",
    "status": "active",
    "game": { "id": 1, "brand": "Connectit", "name": "No3. Hunting" },
    "location": "Online, Zoom",
    "time": "2025-05-10T19:00:00+02:00",
    "price": { "amount": 10, "currency": "EUR", "per": "player" },
    "registration": {
        "form": {
            "openTime": "2025-05-05T12:00:00+02:00",
            "endpoint": {
                "url": "https://your-site.com/reg/1",
                "method": "POST",
                "format": "json",
                "idempotent": true
            },
            "teamsCapacity": 20,
            "guestsCapacity": 100,
            "fields": [
                { "type": "text", "name": "team_name", "required": true, "title": "Team Name", "description": null, "max": 20 },
                { "type": "phone", "name": "phone", "required": true, "title": "Your phone", "description": null },
                { "type": "email", "name": "email", "required": true, "title": "Your email", "description": "We'll send links" },
                { "type": "checkbox", "name": "agree", "required": true, "title": "Do you agree?", "value": 1 },
                { "type": "hidden", "name": "event_id", "value": 1 }
            ]
        }
    }
}
```

## Single Game example

You can find more details [here](GamesAPIv1.md#single-game)

```json
{
    "id": 57,
    "brand": "Detective Games",
    "name": "No3. The Hunt",
    "type": "offline",
    "form": {
        "openTime": "2020-01-01T00:00:00+03:00",
        "endpoint": {
            "url": "https://your-site.com/api/registration",
            "method": "POST",
            "format": "form"
        },
        "fields": [
            { "type": "text", "name": "name", "required": true, "title": "Your name" },
            { "type": "phone", "name": "phone", "required": true, "title": "Your phone" }
        ]
    }
}
```

## Properties description

| Field                 | Required | Default | Description                                              |
|-----------------------|----------|---------|----------------------------------------------------------|
| form                  | false    |         | You can provide it if you want us to send leads to you   |
| form.openTime         | false    | now     | When the form becomes available. Formats are described   |
|                       |          |         | in [Dates and times](EventsAPIv1.md#dates-and-times)     |
| form.teamsCapacity    | false    |         | How many teams the event can take                        |
| form.guestsCapacity   | false    |         | How many people the venue can hold                       |
| **form.endpoint**     | true     |         | Object describing the URL and format of our requests     |
| **form.endpoint.url** | true     |         | URL of your site to which we'll send the requests        |
| form.endpoint.method  | false    | POST    | HTTP method                                              |
| form.endpoint.format  | false    | form    | Available values: "form", "json"                         |
| form.endpoint.idempotent | false | false  | Set it to true once you handle `idempotency_key`, see    |
|                       |          |         | [Sending the same registration twice](#sending-the-same-registration-twice) |
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
| title       | true     | The field's title, which the user can see on the UI. Not needed for "hidden"                       |
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

The only field we add to your own is `idempotency_key`, described
[below](#sending-the-same-registration-twice). Everything needed to verify that the request is ours
travels in headers, see [Request verification](RequestVerification.md).

In case of successful processing, you return the following JSON response:

```json
{"success": true, "registrationId": 123}
```

`registrationId` is the id of the registration you have just created, the same one you return in
`registration.teams[].id` of the [event](EventsAPIv1.md#single-event). Without it neither side can
talk about a particular registration afterwards: we cannot match the lead we sent you to the team in
your event, we cannot tell you which registration a person is asking about, and we cannot recognise
the answer to a repeated request.

If you need the user to pay online for something, you can also provide a "payUrl" with the response:

```json
{"success": true, "registrationId": 123, "payUrl": "https://someurl.com/pay/123"}
```

We send the user to that URL. We do not hear back from the payment provider, so **you tell us the
payment went through by flipping that registration's `status` to `confirmed`** in
`registration.teams[]` the next time we read the event. No callback to build, nothing else to
implement — but if you never change the status, the registration stays `new` for us forever.

In case of error:

```json
{"success":false, "message": "Your phone is incorrect"}
```

The `message` is shown to the person who filled in the form, so write it for them.

Answer such a refusal with `422`. Keep the other status codes for the situations they describe:
`403` if our signature does not check out, `5xx` if something broke on your side. The full table, and
what we do with each answer, is in [Responses and errors](Responses.md).

## Sending the same registration twice

Sooner or later your answer to a registration gets lost: a timeout, a proxy hiccup, a deploy at the
wrong second. From our side a lost answer looks exactly like a lost request, and we cannot tell
whether the team was signed up or not.

There are only two ways out of that, and both are bad unless you help: we retry and risk signing the
same team up twice, or we give up and lose the lead. So we do neither by default — we give you a key
that lets a retry be safe.

### `idempotency_key`

Every registration request carries an `idempotency_key`: a UUID identifying one registration
attempt. **If we send the same registration again, the key is the same.** A different person filling
in the same form gets a different key.

Store it next to the registration and check it before creating a new one:

```php
$key = $_POST['idempotency_key'] ?? null;

$existing = $key ? $this->findRegistrationByIdempotencyKey($key) : null;
if ($existing) {
    // Not a new team: this is the same request again, so repeat the original answer
    return ['success' => true, 'registrationId' => $existing->id];
}
```

Three rules make this work:

* **Repeat the original answer**, with the same `registrationId` and the same status code. A repeated
  request should look to us exactly like the first one.
* **Do not answer with an error.** `{"success": false, "message": "You are already registered"}` is
  the tempting reply and the wrong one: the registration succeeded, and that message would be shown
  to a person whose booking is perfectly fine, telling them something went wrong when nothing did.
* **Keep the keys at least until the event is over.** They are worth nothing afterwards, and a day
  is the shortest sensible retention if you would rather not tie them to events.

### Tell us you handle it

```json
{
    "endpoint": {
        "url": "https://your-site.com/reg/1",
        "method": "POST",
        "format": "json",
        "idempotent": true
    }
}
```

`idempotent: true` means "a repeated `idempotency_key` will not create a second registration".

Only then do we repeat a registration whose answer we never received. Leave it out — the default —
and we never repeat one, which is the safe behaviour but means an answer lost in transit is a lead
lost for good.

Please do not set the flag before you actually store the keys. It reads as a promise, and the whole
point of it is that we act on it.

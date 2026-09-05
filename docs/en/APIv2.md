> **Languages:** English | [Русский](../APIv2.md)

Extrareality API v2
=======

With every request we send these parameters:

* datetime (for example `"2019-05-01 12:00:00"`)
* signature

It is generated from a secret key known only to us and you. You can use it to verify that the request really comes from us, but this check is optional.

The signature is formed as follows:

```php
md5($datetime . $secret)
```

Parameters used:

* datetime — in `"Y-m-d H:i:s"` format (yyyy-mm-dd hh:mm:ss)
* secret — our shared little secret ;)

### Quick links to method descriptions

You must provide us with 2–3 URLs on your site where our API is implemented and where we will send requests:

* [Booking](#booking)
* [Schedule](#schedule)
* [Cancel booking](#cancel-booking) (optional)
* [Update booking](#update-booking) (optional)

Schedule
---

Method: GET

You return a list of all your slots roughly one month ahead, adding an `"extraPrices"` field to each one. It is an array where the key is a condition and the value is a price.

Each slot is an object with the following properties:

* **date** (Y-m-d)
* **time** (H:i)
* **is_free** — (boolean) `true` means the slot is available for booking. If the game time has passed or the slot is already booked, return `false`.
* **extraPrices** — (price array where the _key_ is a condition or player count and the _value_ is the price). If the key is an integer, the system interprets it as a player count (in some cases this may be more convenient for you)

You may also pass additional parameters that we will return to you when booking (in the example, `_our_time_id_`).

Example 1:

```
[
   {
       "date": "2025-05-05",
       "time": "18:30",
       "is_free": true,
       "extraPrices": {
           "2 players": 80,
           "More than 2 players": 110
       },
       "our_time_id": 321
   },
   {
       "date": "2025-05-05",
       "time": "20:00",
       "is_free": false,
       "extraPrices": {
           "2 players": 70,
           "More than 2 players": 100
       },
       "our_time_id": 322
   },
]
```

Example 2, with prices depending on player count:

```
[
   {
       "date": "2025-05-05",
       "time": "18:30",
       "is_free": true,
       "extraPrices": {
           "2": 80,
           "3": 80,
           "4": 120
       },
       "our_time_id": 321
   },
   {
       "date": "2025-05-05",
       "time": "20:00",
       "is_free": false,
       "extraPrices": {
           "2": 80,
           "3": 110,
           "4": 120
       },
       "our_time_id": 322
   },
]
```

In this case our system automatically understands the minimum and maximum number of players allowed for the game.

Note that player counts must be in order. If your array has gaps, for example:

```json
{
  "extraPrices": {
    "2": 80,
    "3": 110,
    "6": 120
  }
}
```

then for missing keys we use the previous value, i.e. your data will be interpreted as:

```json
{
  "extraPrices": {
    "2": 80,
    "3": 110,
    "4": 110,
    "5": 110,
    "6": 120
  },
}
```

Booking
---

Method: POST

The following fields will be sent to the URL you specify:

* comment — customer comment (if any)
* datetime — game date and time in `"Y-m-d H:i:s"` format
* email — may be `null`, so account for that when saving to your database
* name — customer name
* phone — customer phone
* players_num — number of players
* price — game price
* signature — described at the beginning of this document
* source — always `"extrareality"` from us
* uid — unique booking ID on our site
* promo_code — promo code from us, may be empty

Depending on settings, these fields may also be present:

* payment_method — payment method
* game_language — game language
* game_mode — game mode
* additional_services — additional options/services
* services_price — price of additional options/services
* services_commission — our commission for additional options/services
* invoice_data — invoice data

We may also send additional parameters from your schedule slots, such as `our_time_id`, or others by prior agreement.

On success, return a JSON response:

```json
{"success": true}
```

On failure, return:

```json
{"success":false, "message": "error message"}
```

The `message` field contains the error reason.

**The HTTP status code must always be 200, whether you return an error or not.**

Example:

**Request**

```http request
POST https://superquestsite.com/api/quest2/book

name=Ivan
    &comment=Comment
    &datetime=2019-05-01 20:00:00
    &email=test@tut.by
    &our_time_id=124
    &phone=375291234567
    &price=120,
    &signature=5a4fic756e8...6dd04bc9174,
    &source=extrareality
    &uid=854
```

**Response**

```json
{"success": true}
```

or

```json
{"success": false, "message": "Your name is not James Bond" }
```

Update booking
---

Method: POST

Updated booking data will be sent to the URL you specify (for example, if payment status, player count, etc. changed).

These fields are always present:

* uid — unique booking ID on our site
* datetime — game date and time in `"Y-m-d H:i:s"` format
* quest_id — quest ID on our site
* signature — described at the beginning of this document

The following fields are present only if their values changed:

* phone — customer phone
* comment — comment
* price — price
* players_num — number of players
* is_paid — whether paid

On success, return a JSON response:

```json
{"success": true}
```

On failure, return:

```json
{"success":false, "message": "error message"}
```

The `message` field contains the error reason.

**The HTTP status code must always be 200, whether you return an error or not.**

Cancel booking
---

Method: POST

The following fields will be sent to the URL you specify:

* datetime — game date and time in `"Y-m-d H:i:s"` format
* phone — customer phone
* quest_id — quest ID on our site
* uid — unique booking ID on our site
* signature — described at the beginning of this document

On success, return a JSON response:

```json
{"success": true}
```

On failure, return:

```json
{"success":false, "message": "error message"}
```

The `message` field contains the error reason.

**The HTTP status code must always be 200, whether you return an error or not.**

## Fetch review list

In the methods above the request comes from us; here you send a GET request. We recommend sending it no more often than once every 30 minutes.

Request URL:

https://extrareality.by/api2/reviews?quest_id=...

Returns the latest reviews (sorted descending).

**Other possible parameters (besides quest_id):**

* newer_than_id — if set, reviews are fetched starting from this review id (save the maximum id from a response and use it on the next run)
* quantity — number of reviews to fetch, maximum 100 (if less than 1, 50 is used)
* rating_threshold — minimum average rating to include (7.5, 8, etc.)

Response:

```
Content-Type: application/json

[
    {
        "id": 9,
        "datetime": "2019-07-03 10:00:00",
        "name": "Innokenty",
        "text": "Great quest! Freedom for the parrots!",
        "rating": 9.4
    },
    {...},
    {...}
]
```

## Fetch quest rating

Send a GET request to:

https://extrareality.by/api2/rating?quest_id=...

or, for JSON output:

https://extrareality.by/api2/rating?quest_id=...&json=1

In the second case the response looks roughly like this:

```
Content-Type: application/json

{
    "questId": 9,
    "rating": 9.87
}
```

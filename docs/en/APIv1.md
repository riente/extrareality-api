> **Languages:** English | [Русский](../APIv1.md)

More about the API
=======

**Attention! This is an old version of our API.** The latest current version is described [here](APIv2.md). If you are implementing that version, you do not need to read below.

## Quest World Integration

This is not required, but if you work with Quest World and have configured their API, a **simpler option** for you is to slightly extend your schedule output.

For booking, leave everything as is and provide us with the necessary data, for example an md5 code and the endpoint URL.

Regarding the schedule, you return a list of all your slots roughly one month ahead, the same way as for QW, but add an `"extraPrices"` field to each slot containing an array. In it: the key is a condition, the value is a price. This does not affect Quest World integration in any way. Example:

```
[
   {
       "date": "2016-05-05",
       "time": "18:30",
       "is_free": true,
       "price": 3000,
       "extraPrices": {
           "2 человека": 80,
           "Больше 2 человек": 110
       }
   },
   {
       "date": "2016-05-05",
       "time": "20:00",
       "is_free": false,
       "price": 3500,
       "extraPrices": {
           "2 человека": 70,
           "Больше 2 человек": 100
       }
   },
]
```

## Legacy API

You can send us requests as described below. The root URL of our API is https://extrareality.by/api, with _/book_, _/cancel_, and other paths appended.

For many requests you must use mandatory parameters. If they are not required for certain methods, this will be stated explicitly.

#### Quick links to method descriptions

* [Fetch review list](#fetch-review-list)
* [Cancel booking](#cancel-booking) - can be sent to our server if you handle cancellation on your side

#### Required parameters description:

* datetime - date and time in Y-m-d H:i:s format (see individual method descriptions for details)
* owner_id - your quest room id (**not** the one in the catalog; you need to confirm this with us)
* quest_id - quest id for which to fetch reviews (by default the same as in the URL, e.g. for "Похищения" = 12, https://extrareality.by/quest/12), but you can also use your internal ID on your site; in that case let us know
* signature - encrypted signature; how it is formed can be seen in the method [\Extrareality\Client::generateSignature()](https://github.com/riente/extrareality-api/blob/78f8d4d6e535489e6bea80bc3391dfdd78e7c991/src/Client.php#L162)

#### Optional parameters (for any request):

* source - request source; useful if you partner with someone who also implements our API and you want to track who brings you clients (from us source will be extrareality)

## Fetch review list

Can be used no more often than once every 30 minutes.

Request URL:
https://extrareality.by/api/reviews?datetime=...&quest_id=...&owner_id=...&signature=...

Method GET

```
Returns application/json, an array in the form
{
    REVIEW_ID: {
        "datetime": ...,
        "name": ..., // name of the reviewer
        "text": ..., // review text
        "rating": ... // average rating
    },
    REVIEW_ID2: {...}
}
```

Among the [required parameters](#required-parameters-description): datetime contains the time starting from which to fetch reviews.

**Optional parameters:**

* newer_than_id - if specified, reviews are fetched not from the date but from this review id (i.e. when fetching the list, you can save the maximum id and on the next day fetch starting from it)
* quantity - number of reviews to fetch (if less than 1, 50 will be used)
* rating_threshold - minimum average rating for reviews to include (7.5, 8, etc.)

Cancel booking
---

https://extrareality.by/api/cancel

Method: POST

Parameters are the same as [described above](#required-parameters-description); datetime should be the booking time in format 2015-11-06 22:00 or 2015-11-06 22:00:00.

Returns code 200 and text OK if everything is fine, or 400 and error text if something is wrong.

Extrareality API Client
=======

This is a **deprecated** HTTP client library for working with Extrareality. It will be removed soon; there is no point in using it, as it is simpler to write your own implementation.

### Usage example

For example, a booking happens on your site and you want to "notify" Extrareality about it.

```php
use Extrareality\Client;

$config = [
    'secret' => 'somesecretkey', // you need to obtain this from us
    'ownerId' => 123, // your quest room ID in our database (ask us for this too)
];

$questId = 5; // quest ID in your database (if it differs from ours, you should tell us so we will send that one)
$datetime = '2017-04-20 09:00:00'; // time for which the quest is booked

try {
    $client = new Client($config['ownerId'], $config['secret'], $questId);
    // Optionally you can set a custom source or API URL
    // $client->setSource('yoursitename');
    // $client->setApiUrl('https://partner.site/api/');
    $client->book($datetime, $questId);
} catch (Exception $e) {
    // handle possible exceptions
}
```

You also want to receive and handle requests from Extrareality. You have a controller at http://somesite.ru/api and all requests (like /api/book, /api/check, /api/schedule, etc.) are routed to it.

```php
use Extrareality\ApiRequest;
use Extrareality\Response\ScheduleResponse;

$config = [
    'secret' => 'somesecretkey', // you need to obtain this from us
];

$request = new ApiRequest($config['secret']);

try {
    $api = new ApiRequest($config['secret']);
    if ($api->isBooking()) {
        // register the booking in your system
    } elseif ($api->isCancel()) {
        // ...
    } elseif ($api->isSchedule()) {
        $response = new ScheduleResponse();
        
        // Pseudocode
        $myBookings = getBookingRecords();
        foreach ($myBookings as $booking) {
            $response->addBookingToSchedule(
                new \DateTime($booking->getTimestamp()),
                $booking->getName(),
                $booking->getPhone()
            );
        }

        $response->prepare();

        http_response_code($response->getCode());
        header('Content-Type: '.$response->getContentType().'; charset=utf-8');
        echo $response->getMessage();
        exit;
    } elseif ($api->isCheck()) {
        // ...
    }
} catch (Exception $e) {
   // handle
}
```

Extrareality API Client
=======

Примеры в конце документа.

Подробнее об API
=======

**Описание обязательных параметров:**

* datetime - дата и время, начиная с которых нужно забрать отзывы (формат Y-m-d H:i:s)
* owner_id - ваш id квеструма (нужно уточнить у нас)
* quest_id - id квеста, для которого нужно получить отзывы (по умолчанию такой же, как в урле, например у "Похищения" = 12, http://extrareality.by/quest/12), но можно использовать внутренний ID на вашем сайте, тогда сообщите нам об этом
* signature - шифрованная подпись; как формируется, можно глянуть в методе \Extrareality\Client::generateSignature()

**Опциональные параметры _(используются только при получении отзывов, но даже там могут быть опущены)_:**

* newer_than_id - если указан, то отзывы берутся не начиная с даты, а начиная с этого id отзыва (т.е. при получении списка, можно максимальный id сохранять, а на следующий день получать начиная с него)
* quantity - количество отзывов для получения (если меньше 1, то возьмется 50)
* rating_threshold - не ниже какой средней оценки брать отзывы (7.5, 8 и т.д.)

Получить список отзывов
---

Можно использовать не чаще раза в 30 минут.

Адрес для запроса будет такой:
https://extrareality.by/api/reviews?datetime=...&quest_id=...&owner_id=...&signature=...

Метод GET

```
Возвращать будет application/json, массив в виде
{
    REVIEW_ID => {
        "datetime" => ...,
        "name" => ..., // имя, кто оставил
        "text" => ..., // текст отзыва
        "rating" => ... // средняя оценка
    },
    REVIEW_ID2 => {...}
}
```


Забронировать
---

https://extrareality.by/api/book

Метод POST

Параметры те же, как описано выше (которые обязательные), в datetime должно быть время, на которое ставится бронь в формате 2015-11-06 22:00 или 2015-11-06 22:00:00. Также можно передавать параметры "name", "phone", "email", "comment", "players_num" (количество игроков). Соответственно, если запрос идет с нашей стороны на ваш сайт, то эти параметры тоже могут передаваться, и нужно настроить их обработку.
Возвращает код 200 и текст ОК, если все норм, или 400 и текст ошибки, если что-то не так.

Отменить бронь
---

https://extrareality.by/api/cancel

Метод POST

Параметры те же.
Возвращает то же, что и book.

Проверить, есть ли бронь
---

https://extrareality.by/api/check

Метод POST

Параметры те же.
Возвращает массив в json: если свободно, то [booked => 0], 
если занято, то [booked => 1, name => "Some name", phone => "Phone"]

Получить список броней по дате
---

https://extrareality.by/api/schedule

Метод GET

Тут datetime пишется в полном формате, но учитывается только дата, т.е. время можно написать любое.
Возвращает массив в json, в котором ключ это точные дата и время, а значение - массив в виде [time => ..., name => ..., phone => ...]

Кто подключает взаимодействие с нами у себя на сайте, с вашей стороны в идеале хотелось бы тоже иметь возможность вызывать такие же методы, которые возвращают данные в таком же формате.

Примеры
=======

К примеру на вашем сайте происходит бронь, и вы хотите "уведомить" об этом Extrareality.

```php
use Extrareality\Client;

$config = [
    'secret' => 'somesecretkey', // его вам нужно узнать у нас
    'ownerId' => 123, // ID вашего квеструма в нашей базе (тоже спросите у нас)
];

$questId = 5; // ID квеста в вашей базе (если он отличается от нашего, имеет смысл сообщить его нам, тогда мы будет отправлть именно его
$datetime = '2017-04-20 09:00:00'; // время, на которое бронируется квест

try {
    $client = new Client($config['ownerId'], $config['secret'], $questId);
    $client->book($datetime, $quest_id);
} catch (Exception $e) {
    // handle possible exceptions
}
```

Также вы хотите получать и обрабатывать запросы от Extrareality. У вас есть контроллер по адресу http://somesite.ru/api и, скажем, все запросы (вроде /api/book, /api/check, /api/schedule и прочие) направляются на него.

```php
use Extrareality\ApiRequest;
use Extrareality\Response\ScheduleResponse;

$config = [
    'secret' => 'somesecretkey', // его вам нужно узнать у нас
];

$request = new ApiRequest($config['secret']);

try {
    $api = new ApiRequest($config['secret']);
    if ($api->isBooking()) {
        // coming soon
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

Чуть позже будут более подробные примеры.

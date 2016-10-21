Extrareality API Client
=======

Примеры использования будут позже.

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

Параметры те же, как описано выше (которые обязательные), в datetime должно быть время, на которое ставится бронь в формате 2015-11-06 22:00 или 2015-11-06 22:00:00.
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
если занято, то [booked => 1, name => "Some name", phone => "Phone", paid => 0]
paid - оплачено или нет (это на будущее, возможно понадобится)

Получить список броней по дате
---

https://extrareality.by/api/schedule

Метод GET

Тут datetime пишется в полном формате, но учитывается только дата, т.е. время можно написать любое.
Возвращает массив в json, в котором ключ это точные дата и время, а значение - массив в виде [time => ..., name => ..., paid => ..., phone => ...]

Кто подключает взаимодействие с нами у себя на сайте, с вашей стороны по возможности хотелось бы тоже иметь возможность вызывать такие же методы, которые возвращают данные в таком же формате.

> **Languages:** [English](../GamesAPIv1.md) | Русский

# ExtraReality Games API v1

Вы можете реализовать этот API, чтобы предоставлять нам информацию о ваших играх.

Если у вас регулярно проводятся запланированные мероприятия, рассмотрите использование [Events API](EventsAPIv1.md).

Согласно этому API, вам нужно разработать несколько эндпоинтов:

* [Список игр](#games-list)
* [Данные об одной игре](#single-game)
* Эндпоинт для приёма заявок (описан на странице [Form Object](FormObject.md))

## Проверка запросов

Мы подписываем каждый отправляемый вам запрос, чтобы вы могли убедиться, что он действительно от нас, и никогда не отдавали свои данные постороннему, который угадал URL.

Каждый запрос содержит заголовки `X-Source`, `X-Timestamp` и `X-Signature-256`, где последний — это HMAC-SHA256 запроса, ключом которого служит секрет, известный только вам и нам.

**Подробную схему, готовый фрагмент PHP для проверки и типичные ошибки см. в разделе [Проверка запросов](RequestVerification.md).**

Что отвечать и каким кодом статуса — см. [Ответы и ошибки](Responses.md).

## Список игр {#games-list}

Предполагается, что все игры в списке доступны в одном городе (или везде — например, онлайн или с доставкой).

При необходимости можно создать несколько эндпоинтов для каждого города или принимать параметры.
Например:

```
https://your-site.com/api/games/city1
https://your-site.com/api/games?city=1
https://your-site.com/api/games?city=2
```

Эндпоинт должен возвращать массив объектов, каждый из которых содержит данные об игре.

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

| Поле        | Обязательно | Описание                                                                                    |
|-------------|-------------|---------------------------------------------------------------------------------------------|
| **id**      | true        | Уникальный ID игры в вашей системе                                                          |
| **brand**   | true        | Если у вас несколько категорий игр, можно указать одну; может быть всегда одним и тем же    |
| **name**    | true        | Название игры                                                                               |
| **url**     | true        | URL объекта [Single Game](#single-game)                                                     |
| img         | false       | Если у вас есть уникальные постеры или изображения игр                                      |
| description | false       | До 2048 символов                                                                            |

## Одна игра {#single-game}

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

| Поле                  | Обязательно | По умолчанию | Описание                                                              |
|-----------------------|-------------|--------------|-----------------------------------------------------------------------|
| **id**                | true        |              | Уникальный ID игры в вашей системе                                    |
| **brand**             | true        |              | Если у вас несколько категорий игр; может быть всегда одним и тем же  |
| **name**              | true        |              | Название игры, например «Movies #3»                                   |
| **type**              | true        |              | Допустимые значения: «online», «offline»                              |
| locale                | false       | depends      | Язык игры                                                             |
| type                  | false       | offline      | Допустимые значения: online, offline                                  |
| prices                | false       | []           | Массив объектов Price (описаны ниже)                                  |
| **prices[].amount**   | true        |              | Число с плавающей точкой или целое                                    |
| **prices[].currency** | true        |              | [ISO 4217](https://en.wikipedia.org/wiki/ISO_4217)                    |
| prices[].description  | false       |              | Если у вас одна и единственная цена, описание можно опустить          |
| img                   | false       | null         | Если у вас есть уникальные постеры или изображения игр                |
| description           | false       | null         | До 2048 символов                                                      |
| gallery               | false       |              | Массив строковых URL                                                  |
| form                  | false       | null         | Необязательно. Подробно описано в [FormObject](FormObject.md)         |

> **Languages:** [English](../FormObject.md) | Русский

# Описание объекта form в API ExtraReality

В некоторых наших API вы можете передать объект `form`, если хотите, чтобы мы могли отправлять вам заявки.

Например, в [Events API](EventsAPIv1.md#single-event) или в [Games API](GamesAPIv1.md#single-game).

## Быстрые ссылки

* [Пример Single Event](#single-event-example)
* [Пример Single Game](#single-game-example)
* [Описание свойств](#properties-description)
* [Запросы на ваш эндпоинт](#requests-to-your-endpoint)

## Пример одного события {#single-event-example}

Подробнее см. [здесь](EventsAPIv1.md#single-event)

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

## Пример одной игры {#single-game-example}

Подробнее см. [здесь](GamesAPIv1.md#single-game)

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

## Описание свойств {#properties-description}

| Поле                     | Обязательно | По умолчанию | Описание                                                 |
|--------------------------|-------------|--------------|----------------------------------------------------------|
| form                     | false       |              | Можно передать, если хотите получать от нас заявки       |
| form.openTime            | false       | now          | Когда форма становится доступной. Форматы описаны        |
|                          |             |              | в разделе [Dates and times](EventsAPIv1.md#dates-and-times) |
| form.teamsCapacity       | false       |              | Сколько команд может принять мероприятие                 |
| form.guestsCapacity      | false       |              | Сколько человек вмещает площадка                         |
| **form.endpoint**        | true        |              | Объект с URL и форматом наших запросов                   |
| **form.endpoint.url**    | true        |              | URL вашего сайта, на который мы будем отправлять запросы |
| form.endpoint.method     | false       | POST         | HTTP-метод                                               |
| form.endpoint.format     | false       | form         | Допустимые значения: «form», «json»                      |
| form.endpoint.idempotent | false       | false        | Установите true, когда начнёте обрабатывать `idempotency_key`, см. |
|                          |             |              | [Sending the same registration twice](#sending-the-same-registration-twice) |
| **form.fields**          | true        |              | Описано ниже                                             |

### `form.fields` description

Это массив объектов, каждый из которых описывает одно поле формы; мы используем их, чтобы сформировать запрос на ваш эндпоинт.

### Объект поля {#field-object}

| Свойство    | Обязательно | Описание                                                                                           |
|-------------|-------------|----------------------------------------------------------------------------------------------------|
| type        | true        | Возможные значения: text, textarea, number, phone, email, radio, select, checkbox, checkboxes, hidden |
| name        | true        | Атрибут «name» поля — имя переменной, которую мы отправим вам                                      |
| value       | false       | Предопределённое значение для checkbox или hidden                                                  |
| required    | true        | Булево: обязательно ли поле                                                                        |
| title       | true        | Заголовок поля, который пользователь видит в интерфейсе. Для «hidden» не нужен                   |
| description | false       | Можно указать, если хотите подробнее описать поле                                                  |
| max         | false       | Для «text» (макс. символов) и «number» (макс. числовое значение)                                   |
| variants    | false       | Для «radio», «select», «checkboxes»                                                                |

### Descriptions by type

| Тип        | Описание                                                                                  |
|------------|-------------------------------------------------------------------------------------------|
| text       | Простое текстовое поле                                                                    |
| textarea   | Многострочное текстовое поле                                                              |
| number     | Числовое поле, принимающее только числа                                                   |
| phone      | Текстовое поле с проверкой корректности телефона                                          |
| email      | Текстовое поле с проверкой корректности email                                             |
| radio      | Переключатели для выбора одного из предложенных вариантов                                 |
| select     | Выпадающий список для выбора одного из предложенных вариантов                             |
| checkbox   | Один флажок для вопроса да/нет; можно использовать для согласия с условиями               |
| checkboxes | Если пользователь может произвольно выбрать несколько вариантов из списка                 |
| hidden     | Если нужно отправить с запросом предопределённые данные, например ID для отслеживания     |

## Запросы на ваш эндпоинт {#requests-to-your-endpoint}

Мы отправим данные в соответствии с `form.fields` вашего объекта на URL, указанный вами в `form.endpoint.url`.

Единственное поле, которое мы добавляем к вашим, — `idempotency_key`, описанный
[ниже](#sending-the-same-registration-twice). Всё необходимое для проверки, что запрос от нас,
передаётся в заголовках, см. [Проверка запросов](RequestVerification.md).

При успешной обработке вы возвращаете следующий JSON-ответ:

```json
{"success": true, "registrationId": 123}
```

`registrationId` — это ID регистрации, которую вы только что создали; тот же ID вы возвращаете в
`registration.teams[].id` [мероприятия](EventsAPIv1.md#single-event). Без него ни одна из сторон не сможет
говорить о конкретной регистрации: мы не сопоставим отправленную заявку с командой в вашем мероприятии,
не сможем указать, о какой регистрации спрашивает человек, и не распознаем ответ на повторный запрос.

Если пользователю нужно оплатить что-то онлайн, в ответе можно также указать «payUrl»:

```json
{"success": true, "registrationId": 123, "payUrl": "https://someurl.com/pay/123"}
```

Мы направляем пользователя по этому URL. От платёжного провайдера обратной связи мы не получаем, поэтому **вы сообщаете нам об успешной оплате, меняя `status` этой регистрации на `confirmed`** в
`registration.teams[]` при следующем чтении мероприятия. Никаких callback'ов реализовывать не нужно — но если вы никогда не меняете статус, регистрация для нас навсегда остаётся `new`.

В случае ошибки:

```json
{"success":false, "message": "Your phone is incorrect"}
```

`message` показывается человеку, заполнившему форму, поэтому пишите его для него.

На такой отказ отвечайте кодом `422`. Остальные коды статуса — для ситуаций, которые они описывают:
`403`, если подпись не прошла проверку; `5xx`, если что-то сломалось на вашей стороне. Полная таблица и то, что мы делаем с каждым ответом, — в [Ответы и ошибки](Responses.md).

## Повторная отправка той же регистрации {#sending-the-same-registration-twice}

Рано или поздно ваш ответ на регистрацию теряется: таймаут, сбой прокси, деплой в неудачную секунду. С нашей стороны потерянный ответ неотличим от потерянного запроса, и мы не можем понять, была ли команда записана или нет.

Из этого есть только два выхода, и оба плохи, если вы не поможете: мы повторяем запрос и рискуем записать одну команду дважды, или сдаёмся и теряем заявку. По умолчанию мы не делаем ни того ни другого — мы даём вам ключ, который делает повтор безопасным.

### `idempotency_key`

Каждый запрос регистрации содержит `idempotency_key`: UUID, идентифицирующий одну попытку регистрации.
**Если мы отправляем ту же регистрацию снова, ключ тот же.** Другой человек, заполняющий ту же форму, получит другой ключ.

Сохраняйте его рядом с регистрацией и проверяйте перед созданием новой:

```php
$key = $_POST['idempotency_key'] ?? null;

$existing = $key ? $this->findRegistrationByIdempotencyKey($key) : null;
if ($existing) {
    // Not a new team: this is the same request again, so repeat the original answer
    return ['success' => true, 'registrationId' => $existing->id];
}
```

Три правила делают это рабочим:

* **Повторяйте исходный ответ** с тем же `registrationId` и тем же кодом статуса. Повторный запрос для нас должен выглядеть точно так же, как первый.
* **Не отвечайте ошибкой.** `{"success": false, "message": "You are already registered"}` — соблазнительный, но неверный ответ: регистрация прошла успешно, а такое сообщение увидит человек с вполне корректной записью и подумает, что что-то пошло не так, хотя всё в порядке.
* **Храните ключи хотя бы до окончания мероприятия.** После этого они бесполезны; сутки — минимальный разумный срок хранения, если не хотите привязывать их к мероприятиям.

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

`idempotent: true` означает: «повторный `idempotency_key` не создаст вторую регистрацию».

Только тогда мы повторяем регистрацию, ответ на которую так и не получили. Если флаг не указан — значение по умолчанию — мы никогда не повторяем; это безопасно, но ответ, потерянный по пути, означает заявку, потерянную навсегда.

Не устанавливайте флаг, пока вы реально не начнёте хранить ключи. Это обещание, и мы действуем в соответствии с ним.

# ExtraReality Partner APIs

> **Languages:** English | [Русский](README.ru.md)

Integration documentation for partners connecting their booking and event systems to ExtraReality.

## Escape Room booking APIs

| API               | Status | Description | Documentation |
|-------------------|--------|-------------|---------------|
| EscapeRoom API v2 | **Recommended** | Schedule, booking, cancel/update | [RU](docs/APIv2.md) · [EN](docs/en/APIv2.md) |
| EscapeRoom API v1 | Deprecated | Legacy schedule & booking | [RU](docs/APIv1.md) · [EN](docs/en/APIv1.md) |

## Events & games APIs

| API | Status | Description | Documentation |
|-----|--------|-------------|---------------|
| Events API v1 | Current | Quizzes, scheduled events, registrations | [EN](docs/EventsAPIv1.md) · [RU](docs/ru/EventsAPIv1.md) |
| Games API v1 | Current | Game catalog & metadata | [EN](docs/GamesAPIv1.md) · [RU](docs/ru/GamesAPIv1.md) |

## Shared reference

Used by Events API and Games API:

| Topic | Documentation |
|-------|---------------|
| Request verification (HMAC) | [EN](docs/RequestVerification.md) · [RU](docs/ru/RequestVerification.md) |
| Response codes & errors | [EN](docs/Responses.md) · [RU](docs/ru/Responses.md) |
| Form object (leads) | [EN](docs/FormObject.md) · [RU](docs/ru/FormObject.md) |

## PHP client library

This repository also contains a PHP helper library (`src/`).

See also the [documentation index](docs/README.md).

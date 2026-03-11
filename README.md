<div align="center">

**[English](#english) | [Русский](#русский)**

</div>

---

<a name="english"></a>

# Finance — Personal Finance Manager

> A production-ready web application for tracking income, expenses, transfers, and investments, built on **Yii2 + PHP 8.2 + MySQL**, containerized with Docker.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, Yii2 Basic |
| Frontend | Bootstrap 5, Vanilla JS (IIFE modules) |
| Database | MySQL 8, migrations |
| Infrastructure | Docker, Docker Compose, Apache |
| UI Widgets | Kartik-v (Select2, DatePicker, FileInput, ActiveForm) |

## Key Features

- **Multi-account system** — manage multiple accounts with currency support
- **Income & Expenses** — categorized tracking with sources and filters
- **Transfers** — move funds between accounts with automatic balance recalculation
- **Investments & Debts** — dedicated sections for long-term financial goals
- **Statistics** — charts and analytics with period selection
- **Soft delete** — all records are archived, not physically removed
- **AJAX/PJAX navigation** — seamless page transitions without full reload

## Architecture Highlights

- **Thin controllers** — all business logic lives in models and services
- **ActiveRecord with custom Query classes** — `UserQuery`, `AccountQuery`, etc.
- **Reusable traits** — `SoftDeleteTrait`, `UserTrackingTrait` shared across models
- **JS module pattern** — every script is an IIFE with public API, `init()`, `reinitialize()`, and PJAX support
- **Strict MVC** — no business logic in views, no fat controllers
- **PSR-12** compliant codebase

## Project Structure

```
├── controllers/        # Thin controllers (CRUD + validation)
├── models/
│   ├── Account/        # AR + Search + Query per entity
│   ├── Earnings/
│   ├── Expenses/
│   ├── Transfers/
│   └── traits/         # Shared: SoftDelete, UserTracking
├── migrations/         # Full DB history
├── views/              # Partial-based layouts (_form reuse)
└── web/js/
    ├── scripts.js      # Single entry point via AppAsset
    ├── main/           # Shared modules (charts)
    └── pages/          # Controller-scoped page scripts
```

## Quick Start

```bash
cp .env.example .env
docker-compose up -d
docker-compose exec web php yii migrate
```

App: `http://localhost:8080` · phpMyAdmin: `http://localhost:8081`

See [INSTALL.md](INSTALL.md) for full setup guide.

---

<a name="русский"></a>

# Finance — Менеджер личных финансов

> Готовое к продакшену веб-приложение для учёта доходов, расходов, переводов и инвестиций на базе **Yii2 + PHP 8.2 + MySQL**, контейнеризованное в Docker.

## Технологический стек

| Уровень | Технология |
|---|---|
| Backend | PHP 8.2, Yii2 Basic |
| Frontend | Bootstrap 5, Vanilla JS (IIFE-модули) |
| База данных | MySQL 8, миграции |
| Инфраструктура | Docker, Docker Compose, Apache |
| UI-виджеты | Kartik-v (Select2, DatePicker, FileInput, ActiveForm) |

## Ключевые возможности

- **Мультисчётная система** — несколько счетов с поддержкой валют
- **Доходы и расходы** — категоризированный учёт с источниками и фильтрами
- **Переводы** — перемещение средств между счетами с автоматическим пересчётом баланса
- **Инвестиции и долги** — отдельные разделы для долгосрочных финансовых целей
- **Статистика** — графики и аналитика с выбором периода
- **Мягкое удаление** — все записи архивируются, физически не удаляются
- **AJAX/PJAX-навигация** — плавные переходы без полной перезагрузки страницы

## Архитектурные решения

- **Тонкие контроллеры** — вся бизнес-логика в моделях и сервисах
- **ActiveRecord с кастомными Query-классами** — `UserQuery`, `AccountQuery` и т.д.
- **Переиспользуемые трейты** — `SoftDeleteTrait`, `UserTrackingTrait` для нескольких моделей
- **JS-модульный паттерн** — каждый скрипт является IIFE с публичным API, `init()`, `reinitialize()` и поддержкой PJAX
- **Строгий MVC** — никакой бизнес-логики во views, никаких жирных контроллеров
- **PSR-12** — стандарт оформления кода соблюдён

## Структура проекта

```
├── controllers/        # Тонкие контроллеры (CRUD + валидация)
├── models/
│   ├── Account/        # AR + Search + Query на каждую сущность
│   ├── Earnings/
│   ├── Expenses/
│   ├── Transfers/
│   └── traits/         # Общие: SoftDelete, UserTracking
├── migrations/         # Полная история БД
├── views/              # Частичные шаблоны (_form переиспользуется)
└── web/js/
    ├── scripts.js      # Единственная точка входа через AppAsset
    ├── global/         # Общие модули (charts, statistics)
    └── pages/          # Скрипты, привязанные к контроллеру
```

## Быстрый старт

```bash
cp .env.example .env
docker-compose up -d
docker-compose exec web php yii migrate
```

Приложение: `http://localhost:8080` · phpMyAdmin: `http://localhost:8081`

Полное руководство по установке: [INSTALL.md](INSTALL.md)

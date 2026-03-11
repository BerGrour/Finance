# Инструкция по установке проекта Finance

## Первоначальная настройка

### 1. Создание файла .env

Скопировать файл `.env.example` в `.env`:

```bash
cp .env.example .env
```

### 2. Запуск Docker контейнеров

```bash
docker-compose up -d
```

Эта команда создаст и запустит три контейнера:
- `finance_web` - веб-сервер с PHP 8.2 и Apache
- `finance_phpr` - phpmyadmin
- `finance_db` - MySQL 8.0

### 3. Установка зависимостей Composer

```bash
docker-compose exec web composer install
```

**Примечание:** При первой установке Composer может запросить разрешение на использование плагина. Если возникнет ошибка, выполнить:
```bash
docker-compose exec web composer config --no-plugins allow-plugins.yiisoft/yii2-composer true
docker-compose exec web composer install
```

### 4. Проверка подключения к базе данных

Проверка доступности базы данных:

```bash
docker-compose exec db mysql -u finance_user -pfinance_pass finance
```

### 5. Доступ к приложению

Доступен в браузере: http://localhost:8080

### 6. Доступ к phpMyAdmin

phpMyAdmin доступен по адресу: http://localhost:8081

**Данные для входа:**
- Сервер: `db`
- Пользователь: `finance_user` (или `root` для полного доступа)
- Пароль: `finance_pass` (или значение `MYSQL_ROOT_PASSWORD` из `.env` для root)

## Полезные команды

### Просмотр логов
```bash
docker-compose logs -f web
docker-compose logs -f db
```

### Остановка контейнеров
```bash
docker-compose down
```

### Перезапуск контейнеров
```bash
docker-compose restart
```

### Доступ к консоли контейнера
```bash
docker-compose exec web bash
```

### Выполнение консольных команд Yii
```bash
docker-compose exec web php yii
```

## Создание миграций

После создания миграций выполнить:

```bash
docker-compose exec web php yii migrate
```

# Laravel OpenApi Client Generator

Пакет для Laravel, который генерирует http клиенты с бекенд сервисам при помощи [OpenApi Generator](https://openapi-generator.tech/).
На данный момент есть поддержка для двух платформ: PHP и NodeJS.

## Зависимости:
1. Java 8 и выше.
2. npm 5.2 и выше.

## Установка:
1. `composer require --dev greensight/laravel-openapi-client-generator`
2. `php artisan vendor:publish --provider="Greensight\LaravelOpenapiClientGenerator\OpenapiClientGeneratorServiceProvider"` - копирует конфиг генератора в конфиги приложения

## Запуск:
1. Перед запуском убедиться, что структура описания апи соответствует [этим требованиям](https://github.com/greensight/laravel-openapi-client-generator/blob/master/docs/api_schema_requirements.md).

2. Настроить параметры генерации для соответствующих платформ (если дефолтные не подходят).

3. Генерация клиента для nodejs: `php artisan openapi:generate-client-nodejs`, для php: `php artisan openapi:generate-client-php`

4. После успешной генерации по пути `<output_dir>-js|php` для nodejs или php соответственно появится код клиента, который можно запушить в git, в корне будет лежать скрипт git_push который удобно использовать для этого.

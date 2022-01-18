# Laravel OpenApi Client Generator

Пакет для Laravel, который генерирует http клиенты к сервису при помощи [OpenApi Generator](https://openapi-generator.tech/).
На данный момент есть поддержка для двух платформ: PHP и NodeJS.

## Зависимости:
1. Java 8 и выше.
2. npm 5.2 и выше.

## Установка:
1. `composer require --dev ensi/laravel-openapi-client-generator`
2. `php artisan vendor:publish --provider="Greensight\LaravelOpenapiClientGenerator\OpenapiClientGeneratorServiceProvider"` - копирует конфиг генератора в конфиги приложения
3. измените, если требуется, настройки по умолчанию в конфигурационном файле
   

## Запуск:
1. Перед запуском убедиться, что структура описания апи соответствует [этим требованиям](docs/api_schema_requirements.md).

2. Настроить параметры генерации для соответствующих платформ (если дефолтные не подходят).

3. Генерация клиента:
    * [nodejs](docs/nodejs_client_requirements.md)
    * [php](docs/php_client_requirements.md)

4. После успешной генерации по пути `<output_dir_template>-js|php` для nodejs и php соответственно появится код клиента, который можно запушить в git, в корне будет лежать скрипт git_push который удобно использовать для этого. Соответствующие репозитории должны быть уже созданы в соответствующей системе контроля версий. Полное название репозитория формируется так: `<git_user>/<git_repo_template>-js|php` для nodejs и php соответственно.

## Ограничения
Пакет на данный момент не поддерживает генерацию в Windows окружении.

# Laravel OpenApi Client Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ensi/laravel-openapi-client-generator.svg?style=flat-square)](https://packagist.org/packages/ensi/laravel-openapi-client-generator)
[![Tests](https://github.com/ensi-platform/laravel-php-rdkafka/actions/workflows/run-tests.yml/badge.svg?branch=master)](https://github.com/ensi-platform/laravel-php-rdkafka/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ensi/laravel-openapi-client-generator.svg?style=flat-square)](https://packagist.org/packages/ensi/laravel-openapi-client-generator)

A package for Laravel that generates http clients to the service using [OpenApi Generator](https://openapi-generator.tech/).
At the moment, there is support for one platform: PHP.

## Installation

You can install the package via composer:

```bash
composer require ensi/laravel-openapi-client-generator --dev
```

Publish the config file with:

```bash
php artisan vendor:publish --provider="Ensi\LaravelOpenapiClientGenerator\OpenapiClientGeneratorServiceProvider"
```

## Version Compatibility

| Laravel OpenApi Client Generator | Laravel                             | PHP            |
|----------------------------------|-------------------------------------|----------------|
| ^0.0.1                           | ^7.x                                | ^7.1.3         |
| ^0.2.0 - ^0.8.3                  | ^7.x                                | ^7.1.3         |
| ^0.8.4                           | ^7.x                                | ^7.2.0         |
| ^0.9.0                           | ^7.x \|\| ^8.x                      | ^7.2.0         |
| ^0.10.0 - ^0.12.0                | ^7.x \|\| ^8.x                      | ^7.2 \|\| ^8.0 |
| ^0.12.1                          | ^8.x \|\| ^9.x                      | ^7.2 \|\| ^8.0 |
| ^0.13.0                          | ^8.x \|\| ^9.x                      | ^8.1           |
| ^0.13.1                          | ^8.x \|\| ^9.x \|\| ^10.x           | ^8.1           |
| ^0.13.5                          | ^8.x \|\| ^9.x \|\| ^10.x\|\| ^11.x | ^8.1           |
| ^0.14.0                          | ^9.x \|\| ^10.x\|\| ^11.x           | ^8.1           |

## Basic Usage:

1. Before launching, make sure that the api description structure meets [these requirements](docs/api_schema_requirements.md).

2. Configure the generation parameters (if the default ones are not suitable).

3. Client Generation:
    * [php](docs/php_client_requirements.md)

4. After successful generation, the client code will appear along the path `<output_dir_template>-php`, which can be put into git, the git_push script will be at the root, which is convenient to use for this. The corresponding repositories should already be created in the appropriate version control system. The full name of the repository is formed as follows: `<git_user>/<git_repo_template>-php`.

## Limitations

The package currently does not support generation in a Windows environment.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

### Testing

1. composer install
2. npm install
3. composer test

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

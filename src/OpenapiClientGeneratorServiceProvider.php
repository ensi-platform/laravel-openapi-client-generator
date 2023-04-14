<?php

namespace Ensi\LaravelOpenapiClientGenerator;

use Ensi\LaravelOpenapiClientGenerator\Commands\GeneratePhpClient;
use Illuminate\Support\ServiceProvider;

class OpenapiClientGeneratorServiceProvider extends ServiceProvider
{
    private const CONFIG_FILE_NAME = 'openapi-client-generator.php';

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/' . self::CONFIG_FILE_NAME,
            self::CONFIG_FILE_NAME
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/' . self::CONFIG_FILE_NAME => config_path(self::CONFIG_FILE_NAME),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GeneratePhpClient::class,
            ]);
        }
    }
}

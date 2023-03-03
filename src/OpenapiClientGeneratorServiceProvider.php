<?php

namespace Ensi\LaravelOpenapiClientGenerator;

use Ensi\LaravelOpenapiClientGenerator\Commands\GenerateNodeJSClient;

use Ensi\LaravelOpenapiClientGenerator\Commands\GeneratePhpClient;
use Illuminate\Support\ServiceProvider;

class OpenapiClientGeneratorServiceProvider extends ServiceProvider
{
    const CONFIG_FILE_NAME = 'openapi-client-generator.php';

    /**
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/' . self::CONFIG_FILE_NAME,
            self::CONFIG_FILE_NAME
        );
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/' . self::CONFIG_FILE_NAME => config_path(self::CONFIG_FILE_NAME),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateNodeJSClient::class,
                GeneratePhpClient::class,
            ]);
        }
    }
}

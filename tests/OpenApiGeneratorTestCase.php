<?php

namespace Ensi\LaravelOpenapiClientGenerator\Tests;

use Ensi\LaravelOpenapiClientGenerator\OpenapiClientGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class OpenApiGeneratorTestCase extends Orchestra
{
    public static string $outputDirName = '../openapi-test-client';

    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        foreach (glob(static::$outputDirName . '*') as $dirName) {
            $this->rmdir($dirName);
        }
    }

    protected function rmdir(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            if(is_dir("$dir/$file")) {
                $this->rmdir("$dir/$file");
            } else {
                unlink("$dir/$file");
            }
        }

        rmdir($dir);
    }

    protected function getPackageProviders($app): array
    {
        return [
            OpenapiClientGeneratorServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('openapi-client-generator.apidoc_dir', './tests/api-docs');
        config()->set('openapi-client-generator.output_dir_template', static::$outputDirName);
        config()->set('openapi-client-generator.git_user', 'Baristanko');
        config()->set('openapi-client-generator.git_repo_template', 'openapi-client-js-example');
        config()->set('openapi-client-generator.git_host', 'github.com');

        // PHP client params
        config()->set('openapi-client-generator.php_args.params', [
            'apiPackage' => 'Api',
            'invokerPackage' => 'Ensi\\OpenapiClientPHPExample',
            'modelPackage' => 'Dto',
            'packageName' => 'OpenapiClientPHPExample',
        ]);
        config()->set('openapi-client-generator.php_args.composer_name', 'ensi/openapi-client-php-example');

        // JS client params
        config()->set('openapi-client-generator.js_args.params', [
            'npmName' => 'open-api-example-client-js',
            'useES6' => true,
            'useSingleRequestParameter' => true,
            'withInterfaces' => true,
            'typescriptThreePlus' => true,
        ]);
        config()->set('openapi-client-generator.js_args.generate_nestjs_module', true);
    }
}

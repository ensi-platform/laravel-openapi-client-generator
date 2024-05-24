<?php

namespace Ensi\LaravelOpenapiClientGenerator\Tests;

use Ensi\LaravelOpenapiClientGenerator\OpenapiClientGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            OpenapiClientGeneratorServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (glob($this->getOutputDirName() . '*') as $dirName) {
            $this->rmdir($dirName);
        }
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('openapi-client-generator.apidoc_dir', __DIR__ . '/api-docs');
        config()->set('openapi-client-generator.output_dir_template', $this->getOutputDirName());

        config()->set('openapi-client-generator.git_user', 'gitUserTest');
        config()->set('openapi-client-generator.git_repo_template', 'gitRepoTemplateTest');
        config()->set('openapi-client-generator.git_host', 'github.com');

        // PHP client params
        config()->set('openapi-client-generator.php_args.params', [
            'apiPackage' => 'Api',
            'invokerPackage' => 'Ensi\\OpenapiClientPHPExample',
            'modelPackage' => 'Dto',
            'packageName' => 'OpenapiClientPHPExample',
        ]);
        config()->set('openapi-client-generator.php_args.composer_name', 'ensi/openapi-client-php-example');
    }

    protected function getOutputDirName(): string
    {
        return __DIR__ . '/clients';
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
}

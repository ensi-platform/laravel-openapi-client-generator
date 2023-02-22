<?php

use Ensi\LaravelOpenapiClientGenerator\OpenapiClientGeneratorServiceProvider;
use Orchestra\Testbench\TestCase;

class GeneratePhpClientTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
    }

    protected function getEnvironmentSetUp($app): void {
        $app['config']->set('openapi-client-generator.apidoc_dir', ('./tests/api-docs'));
        $app['config']->set('openapi-client-generator.output_dir_template', '../openapi-test-client');
        $app['config']->set('openapi-client-generator.git_user', 'Baristanko');
        $app['config']->set('openapi-client-generator.git_repo_template', 'openapi-client-php-example');
        $app['config']->set('openapi-client-generator.git_host', 'github.com');
        $app['config']->set('openapi-client-generator.php_args.params', [
            'apiPackage' => 'Api',
            'invokerPackage' => 'Baristanko\\OpenapiClientPHPExample',
            'modelPackage' => 'Dto',
            'packageName' => 'OpenapiClientPHPExample'
        ]);
        $app['config']->set('openapi-client-generator.php_args.composer_name', 'ensi/openapi-client-php-example');
    }

    protected function getPackageProviders($app): array
    {
        return [
            OpenapiClientGeneratorServiceProvider::class
        ];
    }

    public function testPushAndPop(): void
    {
        $code = $this->artisan('openapi:generate-client-php');
        $this->assertSame($code, 0);
    }
}

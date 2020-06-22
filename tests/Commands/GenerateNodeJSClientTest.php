<?php

use Orchestra\Testbench\TestCase;

class GenerateNodeJSClientTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
    }

    protected function getEnvironmentSetUp($app): void {
        $app['config']->set('openapi-server-generator.apidoc_dir', ('./tests/api-docs'));
        $app['config']->set('openapi-server-generator.output_dir', '../openapi-test-client');
        $app['config']->set('openapi-server-generator.nodejs_args.params', [
            'npmName' => 'open-api-example-client-ts',
            'useES6' => true,
            'useSingleRequestParameter' => true,
            'withInterfaces' => true,
            'withSeparateModelsAndApi' => true,
            'typescriptThreePlus' => true,
            'apiPackage' => 'api',
            'modelPackage' => 'dto'
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            'Greensight\LaravelOpenapiClientGenerator\OpenapiClientGeneratorServiceProvider'
        ];
    }

    public function testPushAndPop()
    {
        $code = $this->artisan('openapi:generate-client-nodejs');
        $this->assertSame($code, 0);
    }
}

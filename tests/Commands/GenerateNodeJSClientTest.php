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
        $app['config']->set('openapi-client-generator.apidoc_dir', ('./tests/api-docs'));
        $app['config']->set('openapi-client-generator.output_dir', '../openapi-test-client');
        $app['config']->set('openapi-client-generator.git_user', 'Baristanko');
        $app['config']->set('openapi-client-generator.git_repo', 'openapi-client-js-example');
        $app['config']->set('openapi-client-generator.git_host', 'github.com');
        $app['config']->set('openapi-client-generator.js_args.params', [
            'npmName' => 'open-api-example-client-js',
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

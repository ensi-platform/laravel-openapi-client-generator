<?php

use Ensi\LaravelOpenapiClientGenerator\OpenapiClientGeneratorServiceProvider;
use Orchestra\Testbench\TestCase;

class GenerateNodeJSClientTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('openapi-client-generator.apidoc_dir', ('./tests/api-docs'));
        $app['config']->set('openapi-client-generator.output_dir_template', '../openapi-test-client');
        $app['config']->set('openapi-client-generator.git_user', 'Baristanko');
        $app['config']->set('openapi-client-generator.git_repo_template', 'openapi-client-js-example');
        $app['config']->set('openapi-client-generator.git_host', 'github.com');
        $app['config']->set('openapi-client-generator.js_args.params', [
            'npmName' => 'open-api-example-client-js',
            'useES6' => true,
            'useSingleRequestParameter' => true,
            'withInterfaces' => true,
            'typescriptThreePlus' => true,
        ]);
        $app['config']->set('openapi-client-generator.js_args.generate_nestjs_module', true);
    }

    protected function getPackageProviders($app): array
    {
        return [
            OpenapiClientGeneratorServiceProvider::class,
        ];
    }

    public function testPushAndPop(): void
    {
        $code = $this->artisan('openapi:generate-client-nodejs');
        $this->assertSame($code, 0);
    }
}

<?php

namespace Ensi\LaravelOpenapiClientGenerator\Tests\Commands;

use Ensi\LaravelOpenapiClientGenerator\Tests\OpenApiGeneratorTestCase;

class GenerateNodeJSClientOpenApiGeneratorTest extends OpenApiGeneratorTestCase
{
    public function testPushAndPop(): void
    {
        $code = $this->artisan('openapi:generate-client-nodejs');
        $this->assertSame($code, 0);
    }
}

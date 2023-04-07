<?php

namespace Ensi\LaravelOpenapiClientGenerator\Tests\Commands;

use Ensi\LaravelOpenapiClientGenerator\Tests\OpenApiGeneratorTestCase;

class GeneratePhpClientOpenApiGeneratorTest extends OpenApiGeneratorTestCase
{
    public function testPushAndPop(): void
    {
        $code = $this->artisan('openapi:generate-client-php');
        $this->assertSame($code, 0);
    }
}

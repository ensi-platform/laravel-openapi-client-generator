<?php

namespace Ensi\LaravelOpenapiClientGenerator\Tests\Commands;

use Ensi\LaravelOpenapiClientGenerator\Tests\TestCase;

class GeneratePhpClientTest extends TestCase
{
    public function testPushAndPop(): void
    {
        $code = $this->artisan('openapi:generate-client-php');
        $this->assertSame($code, 0);
    }
}

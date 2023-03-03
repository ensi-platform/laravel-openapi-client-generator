<?php

namespace Ensi\LaravelOpenapiClientGenerator\Tests\Commands;

use Ensi\LaravelOpenapiClientGenerator\Tests\TestCase;

class GenerateNodeJSClientTest extends TestCase
{
    public function testPushAndPop(): void
    {
        $code = $this->artisan('openapi:generate-client-nodejs');
        $this->assertSame($code, 0);
    }
}

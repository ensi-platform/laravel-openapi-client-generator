<?php

use Ensi\LaravelOpenapiClientGenerator\Commands\GeneratePhpClient;
use Ensi\LaravelOpenapiClientGenerator\Tests\TestCase;

use function Pest\Laravel\artisan;
use function PHPUnit\Framework\assertSame;

test('GeneratePhpClient success', function () {
    /** @var TestCase $this */

    $code = artisan(GeneratePhpClient::class);

    assertSame($code, 0);
});

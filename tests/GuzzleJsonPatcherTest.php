<?php

use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\GuzzleJsonPatcher;

test('GuzzleJsonPatcher uses native JSON encoding and rejects invalid data', function () {
    $file = tempnam(sys_get_temp_dir(), 'guzzle_json_');
    $fixture = <<<'PHP'
<?php
return static function ($formParams) {
    return \GuzzleHttp\json_encode($formParams);
};
PHP;

    try {
        file_put_contents($file, $fixture);
        $patcher = new GuzzleJsonPatcher($file);
        $patcher->patch();
        $content = file_get_contents($file);
        $encode = require $file;

        expect($encode(['name' => 'example']))->toBe('{"name":"example"}')
            ->and(fn () => $encode(["\xB1"]))->toThrow(JsonException::class);

        $patcher->patch();
        expect(file_get_contents($file))->toBe($content);

        file_put_contents($file, str_replace(
            '\\GuzzleHttp\\json_encode($formParams)',
            '\\GuzzleHttp\\json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody))',
            $fixture
        ));
        $patcher->patch();
        expect(file_get_contents($file))
            ->toContain('\json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody), JSON_THROW_ON_ERROR)')
            ->not->toContain('\\GuzzleHttp\\json_encode(');
    } finally {
        unlink($file);
    }
});

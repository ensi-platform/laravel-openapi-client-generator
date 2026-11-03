<?php

use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\GuzzleJsonPatcher;
use GuzzleHttp\Exception\InvalidArgumentException;

test('GuzzleJsonPatcher preserves JSON output and error behavior', function (string $argument) {
    $file = tempnam(sys_get_temp_dir(), 'guzzle_json_');
    $fixture = <<<'PHP'
<?php
namespace {{namespace}};

class ObjectSerializer
{
    public static function sanitizeForSerialization($data)
    {
        return $data;
    }
}

return static function ($data) {
    $_tempBody = $data;
    $formParams = $data;
    $httpBody = \GuzzleHttp\json_encode({{argument}});

    return $httpBody;
};
PHP;
    $fixture = str_replace(
        ['{{namespace}}', '{{argument}}'],
        ['GuzzleJsonFixture' . str_replace('.', '', uniqid('', true)), $argument],
        $fixture
    );

    try {
        file_put_contents($file, $fixture);
        $patcher = new GuzzleJsonPatcher($file);
        $patcher->patch();
        $content = file_get_contents($file);
        $encode = require $file;

        expect($content)->not->toContain('JSON_THROW_ON_ERROR', '\\GuzzleHttp\\json_encode(');

        foreach ([['name' => 'example'], [], null, false, 'строка', 123] as $value) {
            expect($encode($value))->toBe(json_encode($value));
        }

        foreach ([["\xB1"], [INF]] as $value) {
            json_encode($value);
            $message = 'json_encode error: ' . json_last_error_msg();

            expect(fn () => $encode($value))->toThrow(InvalidArgumentException::class, $message);
        }

        // A successful call must not inherit the previous encoding error.
        expect($encode(['ok' => true]))->toBe('{"ok":true}');

        $patcher->patch();
        expect(file_get_contents($file))->toBe($content);
    } finally {
        unlink($file);
    }
})->with([
    'request body' => 'ObjectSerializer::sanitizeForSerialization($_tempBody)',
    'form parameters' => '$formParams',
]);

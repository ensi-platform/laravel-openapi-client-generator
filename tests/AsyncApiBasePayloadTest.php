<?php

function requireAsyncBasePayload(array $dates): string
{
    $packageName = 'Ensi\\LaravelOpenapiClientGenerator\\Tests\\Fixtures\\AsyncPayload'
        . str_replace('.', '', uniqid('', true));
    $template = file_get_contents(__DIR__ . '/../templates/AsyncAPI/php-template/template/Messages/Listen/Payloads/BasePayload.php');
    $content = str_replace('{{ params.packageName | safe }}', $packageName, $template);
    $datesExport = var_export($dates, true);
    $content .= <<<PHP

class TestPayload extends BasePayload
{
    protected array \$dates = {$datesExport};
}
PHP;

    $file = tempnam(sys_get_temp_dir(), 'async_base_payload_');
    file_put_contents($file, $content);
    require_once $file;

    return "{$packageName}\\Messages\\Listen\\Payloads\\TestPayload";
}

test('AsyncAPI BasePayload serializes DateTime as UTC Z with microseconds', function () {
    $className = requireAsyncBasePayload(['created_at' => 'date-time']);
    $payload = new $className([
        'created_at' => new DateTimeImmutable('2026-08-10T15:00:00.123456+03:00'),
    ]);

    expect($payload->toArray())->toBe([
        'created_at' => '2026-08-10T12:00:00.123456Z',
    ]);
});

test('AsyncAPI BasePayload keeps date format in original timezone', function () {
    $className = requireAsyncBasePayload(['created_on' => 'date']);
    $payload = new $className([
        'created_on' => new DateTimeImmutable('2026-08-10T00:30:00.000000+03:00'),
    ]);

    expect($payload->toArray())->toBe([
        'created_on' => '2026-08-10',
    ]);
});

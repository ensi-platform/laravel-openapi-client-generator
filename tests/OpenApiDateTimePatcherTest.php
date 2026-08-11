<?php

use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\OpenApiDateTimePatcher;

function objectSerializerFixture(string $className, string $dateTimeType = '\DateTime'): string
{
    return strtr(<<<'PHP'
<?php

class {{ className }}
{
    private static $dateTimeFormat = \DateTime::ATOM;

    public static function sanitizeForSerialization($data, ?string $format = null): mixed
    {
        if ($data instanceof {{ dateTimeType }}) {
            return ($format === 'date') ? $data->format('Y-m-d') : $data->format(self::$dateTimeFormat);
        }

        return $data;
    }

    public static function sanitizeQueryValue(string $paramName, $value): array
    {
        return ["{$paramName}" => $value->format(self::$dateTimeFormat)];
    }

    public static function serializeValue($value): string
    {
        return $value->format(self::$dateTimeFormat);
    }

    public static function sanitizeFilename($filename)
    {
        return $filename;
    }
}
PHP, [
        '{{ className }}' => $className,
        '{{ dateTimeType }}' => $dateTimeType,
    ]);
}

function patchObjectSerializerFixture(string $content): string
{
    $file = tempnam(sys_get_temp_dir(), 'object_serializer_');
    file_put_contents($file, $content);

    (new OpenApiDateTimePatcher($file))->patch();

    return file_get_contents($file);
}

function requirePatchedObjectSerializer(string $dateTimeType = '\DateTime'): string
{
    $className = 'ObjectSerializer' . str_replace('.', '', uniqid('', true));
    $file = tempnam(sys_get_temp_dir(), 'object_serializer_');
    file_put_contents($file, objectSerializerFixture($className, $dateTimeType));

    (new OpenApiDateTimePatcher($file))->patch();

    require_once $file;

    return $className;
}

test('OpenApiDateTimePatcher serializes DateTime as UTC Z with microseconds', function () {
    $className = requirePatchedObjectSerializer();
    $dateTime = new DateTimeImmutable('2026-08-10T15:00:00.123456+03:00');

    expect($className::sanitizeForSerialization($dateTime))->toBe('2026-08-10T12:00:00.123456Z')
        ->and($className::sanitizeQueryValue('created_at', $dateTime))->toBe([
            'created_at' => '2026-08-10T12:00:00.123456Z',
        ])
        ->and($className::serializeValue($dateTime))->toBe('2026-08-10T12:00:00.123456Z');
});

test('OpenApiDateTimePatcher keeps date format in original timezone', function () {
    $className = requirePatchedObjectSerializer();
    $dateTime = new DateTimeImmutable('2026-08-10T00:30:00.000000+03:00');

    expect($className::sanitizeForSerialization($dateTime, 'date'))->toBe('2026-08-10');
});

test('OpenApiDateTimePatcher fails when expected ObjectSerializer fragment is missing', function () {
    $className = 'BrokenObjectSerializer' . str_replace('.', '', uniqid('', true));
    $content = str_replace(
        'return ["{$paramName}" => $value->format(self::$dateTimeFormat)];',
        <<<'PHP'
        $formattedValue = $value->format(self::$dateTimeFormat);

        return ["{$paramName}" => $formattedValue];
PHP,
        objectSerializerFixture($className)
    );

    expect(fn () => patchObjectSerializerFixture($content))
        ->toThrow(Exception::class, 'unsafe DateTime formatting');
});

test('OpenApiDateTimePatcher does not fail when optional query DateTime branch is absent', function () {
    $className = 'ObjectSerializerWithoutQueryDate' . str_replace('.', '', uniqid('', true));
    $content = str_replace(
        <<<'PHP'

    public static function sanitizeQueryValue(string $paramName, $value): array
    {
        return ["{$paramName}" => $value->format(self::$dateTimeFormat)];
    }
PHP,
        '',
        objectSerializerFixture($className)
    );

    expect(patchObjectSerializerFixture($content))->toContain('function formatDateTime(');
});

test('OpenApiDateTimePatcher does not duplicate existing DateTimeInterface type', function () {
    $className = 'DateTimeInterfaceObjectSerializer' . str_replace('.', '', uniqid('', true));
    $content = patchObjectSerializerFixture(objectSerializerFixture($className, '\DateTimeInterface'));

    expect($content)->toContain('instanceof \DateTimeInterface')
        ->and($content)->not->toContain('DateTimeInterfaceInterface');
});

test('OpenApiDateTimePatcher guarantees formatDateTime method exists after patch', function () {
    $className = 'FormatDateTimeObjectSerializer' . str_replace('.', '', uniqid('', true));
    $content = patchObjectSerializerFixture(objectSerializerFixture($className));

    expect($content)->toContain('function formatDateTime(');
});

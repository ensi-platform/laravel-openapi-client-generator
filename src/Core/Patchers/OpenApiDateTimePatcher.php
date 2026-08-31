<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

use Exception;

class OpenApiDateTimePatcher extends PhpClassPatcher
{
    public function __construct(protected string $objectSerializerFile)
    {
    }

    /** @throws Exception */
    public function patch(): void
    {
        if (!file_exists($this->objectSerializerFile)) {
            throw new Exception("{$this->objectSerializerFile} not exists");
        }

        $content = file_get_contents($this->objectSerializerFile);

        $content = $this->patchDefaultDateTimeFormat($content);
        $content = $this->patchDateTimeInterfaceSupport($content);
        $content = $this->patchDateTimeFormatting($content);
        $content = $this->addFormatDateTimeMethod($content);

        file_put_contents($this->objectSerializerFile, $content);
    }

    protected function patchDefaultDateTimeFormat(string $content): string
    {
        return $this->replaceRequiredValue(
            $content,
            'private static $dateTimeFormat = \DateTime::ATOM;',
            "private static \$dateTimeFormat = 'Y-m-d\\\\TH:i:s.u\\\\Z';",
            'default DateTime format'
        );
    }

    protected function patchDateTimeInterfaceSupport(string $content): string
    {
        $pattern = '/instanceof\s+\\\\DateTime(?!Interface\b)/';
        $patchedContent = $this->replaceRequiredPattern(
            $content,
            $pattern,
            'instanceof \DateTimeInterface',
            'DateTimeInterface support',
            str_contains($content, 'instanceof \DateTimeInterface')
        );

        if (str_contains($patchedContent, 'DateTimeInterfaceInterface')) {
            throw new Exception('DateTimeInterface support patch produced invalid DateTimeInterfaceInterface type');
        }

        return $patchedContent;
    }

    protected function patchDateTimeFormatting(string $content): string
    {
        $content = $this->replaceRequiredValue(
            $content,
            "return (\$format === 'date') ? \$data->format('Y-m-d') : \$data->format(self::\$dateTimeFormat);",
            "return (\$format === 'date') ? \$data->format('Y-m-d') : self::formatDateTime(\$data);",
            'sanitizeForSerialization DateTime formatting'
        );

        $content = $this->replaceValue(
            $content,
            'return ["{$paramName}" => $value->format(self::$dateTimeFormat)];',
            'return ["{$paramName}" => self::formatDateTime($value)];'
        );

        $content = $this->replaceValue(
            $content,
            'return $value->format(self::$dateTimeFormat);',
            'return self::formatDateTime($value);'
        );

        return $this->assertNoUnsafeDateTimeFormatting($content);
    }

    protected function addFormatDateTimeMethod(string $content): string
    {
        $method = <<<'PHP'
    private static function formatDateTime(\DateTimeInterface $dateTime)
    {
        $utcDateTime = \DateTimeImmutable::createFromFormat(
            'U.u',
            $dateTime->format('U.u'),
            new \DateTimeZone('UTC')
        );

        if ($utcDateTime === false) {
            $utcDateTime = new \DateTimeImmutable('@' . $dateTime->getTimestamp());
        }

        return $utcDateTime->setTimezone(new \DateTimeZone('UTC'))->format(self::$dateTimeFormat);
    }

PHP;

        if (str_contains($content, 'function formatDateTime(')) {
            return $this->assertFormatDateTimeMethodExists($content);
        }

        $content = $this->insertBeforeClassClosingBrace($content, $method, 'formatDateTime method');

        return $this->assertFormatDateTimeMethodExists($content);
    }

    /** @throws Exception */
    protected function replaceRequiredValue(
        string $content,
        string $oldValue,
        string $newValue,
        string $description
    ): string {
        $count = 0;
        $patchedContent = str_replace($oldValue, $newValue, $content, $count);

        if ($count === 0) {
            throw new Exception("Не удалось пропатчить {$description}");
        }

        return $patchedContent;
    }

    /** @throws Exception */
    protected function replaceRequiredPattern(
        string $content,
        string $pattern,
        string $newValue,
        string $description,
        bool $allowNoReplacement = false
    ): string {
        $count = 0;
        $patchedContent = preg_replace($pattern, $newValue, $content, -1, $count);

        if ($patchedContent === null) {
            throw new Exception("Не удалось применить регулярное выражение для {$description}");
        }

        if ($count === 0 && !$allowNoReplacement) {
            throw new Exception("Не удалось пропатчить {$description}");
        }

        return $patchedContent;
    }

    /** @throws Exception */
    protected function assertFormatDateTimeMethodExists(string $content): string
    {
        if (!str_contains($content, 'function formatDateTime(')) {
            throw new Exception('Не удалось добавить formatDateTime');
        }

        return $content;
    }

    /** @throws Exception */
    protected function assertNoUnsafeDateTimeFormatting(string $content): string
    {
        if (str_contains($content, '->format(self::$dateTimeFormat)')) {
            throw new Exception('ObjectSerializer contains unsafe DateTime formatting without UTC conversion');
        }

        return $content;
    }

    /** @throws Exception */
    protected function insertBeforeClassClosingBrace(string $content, string $value, string $description): string
    {
        $position = strrpos($content, "\n}");
        if ($position === false) {
            throw new Exception("Не удалось найти точку вставки для {$description}");
        }

        return substr_replace($content, "\n\n" . rtrim($value) . "\n", $position, 0);
    }
}

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
        return $this->replaceValue(
            $content,
            'private static $dateTimeFormat = \DateTime::ATOM;',
            "private static \$dateTimeFormat = 'Y-m-d\\\\TH:i:s.u\\\\Z';"
        );
    }

    protected function patchDateTimeInterfaceSupport(string $content): string
    {
        return $this->replaceValue(
            $content,
            'instanceof \DateTime',
            'instanceof \DateTimeInterface'
        );
    }

    protected function patchDateTimeFormatting(string $content): string
    {
        $content = $this->replaceValue(
            $content,
            "return (\$format === 'date') ? \$data->format('Y-m-d') : \$data->format(self::\$dateTimeFormat);",
            "return (\$format === 'date') ? \$data->format('Y-m-d') : self::formatDateTime(\$data);"
        );

        $content = $this->replaceValue(
            $content,
            'return ["{$paramName}" => $value->format(self::$dateTimeFormat)];',
            'return ["{$paramName}" => self::formatDateTime($value)];'
        );

        return $this->replaceValue(
            $content,
            'return $value->format(self::$dateTimeFormat);',
            'return self::formatDateTime($value);'
        );
    }

    protected function addFormatDateTimeMethod(string $content): string
    {
        if (str_contains($content, 'function formatDateTime(')) {
            return $content;
        }

        $method = <<<'PHP'
    private static function formatDateTime(\DateTimeInterface $dateTime)
    {
        $utcDateTime = \DateTimeImmutable::createFromFormat(
            'U.u',
            $dateTime->format('U.u'),
            new \DateTimeZone('UTC')
        );

        if (false === $utcDateTime) {
            $utcDateTime = new \DateTimeImmutable('@' . $dateTime->getTimestamp());
        }

        return $utcDateTime->setTimezone(new \DateTimeZone('UTC'))->format(self::$dateTimeFormat);
    }

PHP;

        $sanitizeFilenameDocBlock = <<<'PHP'
    /**
     * Sanitize filename by removing path.
PHP;

        if (str_contains($content, $sanitizeFilenameDocBlock)) {
            return $this->replaceValue($content, $sanitizeFilenameDocBlock, $method . $sanitizeFilenameDocBlock);
        }

        return $this->replaceValue(
            $content,
            '    public static function sanitizeFilename($filename)',
            $method . '    public static function sanitizeFilename($filename)'
        );
    }
}

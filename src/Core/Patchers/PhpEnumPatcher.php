<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

use Illuminate\Support\Str;

class PhpEnumPatcher extends EnumPatcher {

    public function __construct(string $enumFile, string $apidocDir)
    {
        parent::__construct($enumFile, $apidocDir);
    }

    protected function getSpecificationName(): string
    {
        return $this->toSnakeCase(basename($this->enumFile, '.php'));
    }

    protected function patchEnumFile(array $constants): void
    {
        $enum = file_get_contents($this->enumFile);

        if ($constants !== null) {

            foreach ($constants as $constant) {
                $enum = $this->patchConstantProperties(
                    $enum,
                    $constant['value'],
                    Str::upper($constant['name']),
                    $constant['title']
                );
            }
        }

        file_put_contents($this->enumFile, $enum);
    }

    private function patchConstantProperties(string $enum, string $value, string $name, string $title): string
    {
        $enum = preg_replace(
            '/' . "const $value = $value;" .'/m',
            "public const $name = $value; // $title",
            $enum
        );

        $enum = preg_replace(
            '/' . "self::$value" .  '/m',
            "self::$name",
            $enum
        );

        return $enum;
    }

    private function toSnakeCase(string $str): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }
}

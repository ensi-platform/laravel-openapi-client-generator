<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

use Illuminate\Support\Str;

class NodeJSEnumPatcher extends EnumPatcher
{
    protected function getSpecificationName(): string
    {
        return $this->toSnakeCase(basename($this->enumFile, '.ts'));
    }

    protected function patchEnumFile(array $constants): void
    {
        if (!file_exists($this->enumFile)) {
            return;
        }

        $enum = file_get_contents($this->enumFile);

        if ($constants !== null) {
            foreach ($constants as $constant) {
                $enum = $this->patchProperties(
                    $enum,
                    $constant['value'],
                    $this->getEnumPropertyName($constant['name'])
                );
            }
        }

        file_put_contents($this->enumFile, $enum);
    }

    private function patchProperties(string $enum, string $value, string $name): string
    {
        $enum = preg_replace(
            '/' . "NUMBER_$value = $value" . '/m',
            "$name = $value",
            $enum
        );

        return $enum;
    }

    private function toSnakeCase(string $str): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }

    private function getEnumPropertyName(string $name): string
    {
        return Str::ucfirst(Str::camel(Str::lower($name)));
    }
}

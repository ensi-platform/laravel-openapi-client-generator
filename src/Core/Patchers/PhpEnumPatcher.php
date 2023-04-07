<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

use Illuminate\Support\Str;

class PhpEnumPatcher extends EnumPatcher
{
    protected function getSpecificationName(): string
    {
        return $this->toSnakeCase(basename($this->enumFile, '.php'));
    }

    protected function patchEnumFile(array $constants): void
    {
        if (!file_exists($this->enumFile)) {
            return;
        }

        $enum = file_get_contents($this->enumFile);

        foreach ($constants as $constant) {
            $enum = $this->patchConstantProperties(
                $enum,
                $constant['value'],
                Str::upper($constant['name']),
                $constant['title']
            );
        }

        file_put_contents($this->enumFile, $enum);
    }

    private function patchConstantProperties(string $enum, string $value, string $name, string $title): string
    {
        // Do some preg replace here to change something

        return $enum;
    }

    private function toSnakeCase(string $str): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }
}

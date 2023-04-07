<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

use Exception;

abstract class EnumPatcher
{
    public function __construct(
        protected string $enumFile,
    ) {
    }

    /** @throws Exception */
    public function patch(): void
    {
        if (!file_exists($this->enumFile)) {
            throw new Exception("$this->enumFile not exists");
        }

        $constants = $this->getConstantsFromSpecification($this->enumFile);

        $this->patchEnumFile($constants);
    }

    abstract protected function getSpecificationName(): string;

    abstract protected function patchEnumFile(array $constants): void;

    protected function getConstantsFromSpecification(string $specification): array
    {
        preg_match_all(
            '/\s-\s(?<value>[\d]+)\s#\s(?<name>[\w]+)\s\|\s(?<title>.+)/mu',
            file_get_contents($specification),
            $constants,
            PREG_SET_ORDER
        );

        return $constants;
    }
}

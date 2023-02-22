<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

use Exception;

abstract class EnumPatcher
{
    public function __construct(
        protected string $enumFile,
        protected array $enumsPathList,
    ) {
    }

    /** @throws Exception */
    public function patch(): void
    {
        $specificationName = $this->getSpecificationName() . '.yaml';
        if (!isset($this->enumsPathList[$specificationName])) {
            throw new Exception("$specificationName not found in in enumsPathList");
        }

        $specificationFile = $this->enumsPathList[$specificationName] . DIRECTORY_SEPARATOR . $specificationName;

        if (!file_exists($specificationFile)) {
            throw new Exception("$specificationFile not exists");
        }

        $constants = $this->getConstantsFromSpecification($specificationFile);

        $this->patchEnumFile($constants);
    }

    protected abstract function getSpecificationName(): string;

    protected abstract function patchEnumFile(array $constants): void;

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

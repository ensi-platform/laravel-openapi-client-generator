<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

abstract class EnumPatcher {

    /**
     * @var string
     */
    protected $enumFile;

    /**
     * @var string
     */
    protected $apidocDir;

    public function __construct(string $enumFile, string $apidocDir)
    {
        $this->enumFile = $enumFile;
        $this->apidocDir = $apidocDir;
    }

    public function patch(): void {
        $specificationFile = $this->apidocDir . DIRECTORY_SEPARATOR . $this->getSpecificationName() . '.yaml';
        if (!file_exists($specificationFile)) {
            return;
        }

        $constants = $this->getConstantsFromSpecification($specificationFile);

        $this->patchEnumFile($constants);
    }

    protected abstract function getSpecificationName(): string;

    protected abstract function patchEnumFile(array $constants): void;

    protected function getConstantsFromSpecification(string $specification) {
        preg_match_all(
            '/\s-\s(?<value>[\d]+)\s#\s(?<name>[\w]+)\s\|\s(?<title>.+)/mu',
            file_get_contents($specification),
            $constants,
            PREG_SET_ORDER
        );

        return $constants;
    }
}

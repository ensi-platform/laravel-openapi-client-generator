<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

use Exception;

class PhpEnumPatcher extends PhpClassPatcher
{
    public function __construct(protected string $enumFile)
    {
    }

    /** @throws Exception */
    public function patch(): void
    {
        if (!file_exists($this->enumFile)) {
            throw new Exception("$this->enumFile not exists");
        }

        $content = file_get_contents($this->enumFile);

        $content = $this->escapingResponseDescription($content);

        file_put_contents($this->enumFile, $content);
    }

    /** @throws Exception */
    protected function escapingResponseDescription(string $content): string
    {
        $method = $this->getMethod('getDescriptions', 'array', $content);

        $descriptions = $this->parsingResponseDescription($method);

        $escapingMethod = $method;
        foreach ($descriptions as $description) {
            $escapedDescription = $this->escapingString($description);
            $escapingMethod = $this->replaceValue($escapingMethod, $description, $escapedDescription);
        }

        return $this->replaceValue($content, $method, $escapingMethod);
    }

    protected function parsingResponseDescription(string $context): array
    {
        return preg_match_all(
            '/self::.*=> ([\'|\"].*[\'|\"]),\\n/smU',
            $context,
            $constants
        ) ? $constants[1] : [];
    }
}

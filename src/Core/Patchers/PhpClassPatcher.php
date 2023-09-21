<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

use Exception;

abstract class PhpClassPatcher
{
    public function getMethod(string $name, string $returnType, string $content): string
    {
        $pattern = "/([public|protected|private]( static)? function {$name}\(\): {$returnType}.*})/sU";
        if (!preg_match($pattern, $content, $matches)) {
            throw new Exception("Не удалось найти метод $name");
        }

        return $matches[1];
    }

    public function escapingString(string $string): string
    {
        return match (true) {
            str_starts_with($string, "'") => $this->replacePatternWithinString($string, "/[^\\\\]\K'/", "\\'"),
            str_starts_with($string, '"') => $this->replacePatternWithinString($string, "/[^\\\\]\K\"/", '\\"'),
            default => $string
        };
    }

    public function replaceValue(string $context, string $oldValue, string $newValue): string
    {
        return str_replace($oldValue, $newValue, $context);
    }

    public function replacePattern(string $context, string $pattern, string $newValue): string
    {
        return preg_replace($pattern, $newValue, $context);
    }

    public function replacePatternWithinString(string $context, string $pattern, string $newValue): string
    {
        $subString = substr($context, 1, -1);
        $replacedSubString = $this->replacePattern($subString, $pattern, $newValue);

        return $this->replaceValue($context, $subString, $replacedSubString);
    }
}

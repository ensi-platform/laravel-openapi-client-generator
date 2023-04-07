<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

class TypeScriptConfigPatcher extends PackageManifestPatcher
{
    protected string $manifestName = 'tsconfig.json';

    protected function applyPatchers(array $tsConfig): array
    {
        $tsConfig = $this->patchTargetJsVersion($tsConfig);
        $tsConfig = $this->patchDecorators($tsConfig);

        return $tsConfig;
    }

    private function patchTargetJsVersion(array $tsConfig): array
    {
        $tsConfig['compilerOptions']['target'] = 'es2017';

        return $tsConfig;
    }

    private function patchDecorators(array $tsConfig): array
    {
        $tsConfig['compilerOptions']['emitDecoratorMetadata'] = true;
        $tsConfig['compilerOptions']['experimentalDecorators'] = true;

        return $tsConfig;
    }
}

<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

class TypeScriptConfigPatcher extends PackageManifestPatcher {

    /**
     * @var string
     */
    protected $manifestName = 'tsconfig.json';

    protected function applyPatchers($tsConfig)
    {
        $tsConfig = $this->patchTargetJsVersion($tsConfig);
        $tsConfig = $this->patchDecorators($tsConfig);

        return $tsConfig;
    }

    private function patchTargetJsVersion($tsConfig)
    {
        $tsConfig['compilerOptions']['target'] = 'es2017';
        return $tsConfig;
    }

    private function patchDecorators($tsConfig)
    {
        $tsConfig['compilerOptions']['emitDecoratorMetadata'] = true;
        $tsConfig['compilerOptions']['experimentalDecorators'] = true;
        return $tsConfig;
    }
}

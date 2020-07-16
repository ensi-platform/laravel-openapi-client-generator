<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

class ComposerPackagePatcher extends PackageManifestPatcher {

    /**
     * @var string
     */
    protected $manifestName = 'composer.json';

    protected function applyPatchers($manifest)
    {
        $manifest = $this->patchLicense($manifest);
        $manifest = $this->patchRequire($manifest);

        return $manifest;
    }

    protected function patchRequire($manifest)
    {
        $manifest['require']['laravel/framework'] = '^7.10';
        return $manifest;
    }
}

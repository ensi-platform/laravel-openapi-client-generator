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

        return $manifest;
    }
}

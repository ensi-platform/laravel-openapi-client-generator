<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

class NpmPackagePatcher extends PackageManifestPatcher {

    /**
     * @var string
     */
    protected $manifestName = 'package.json';

    protected function applyPatchers($manifest)
    {
        $manifest = $this->patchScripts($manifest);
        $manifest = $this->patchLicense($manifest);

        return $manifest;
    }

    private function patchScripts($packageJson)
    {
        $packageJson['scripts']['prepare'] = 'npm run build';
        return $packageJson;
    }
}

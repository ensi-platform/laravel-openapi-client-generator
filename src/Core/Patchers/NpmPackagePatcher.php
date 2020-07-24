<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

class NpmPackagePatcher extends PackageManifestPatcher {

    /**
     * @var string
     */
    protected $manifestName = 'package.json';

    protected function applyPatchers($packageJson)
    {
        $packageJson = $this->patchScripts($packageJson);
        $packageJson = $this->patchLicense($packageJson);
        $packageJson = $this->patchDependencies($packageJson);

        return $packageJson;
    }

    private function patchScripts($packageJson)
    {
        $packageJson['scripts']['prepare'] = 'npm run build';
        return $packageJson;
    }

    private function patchDependencies($packageJson)
    {
        $packageJson['dependencies']['@nestjs/common'] = '7.0.0';
        $packageJson['dependencies']['@nestjs/config'] = '0.5.0';
        $packageJson['dependencies']['rxjs'] = "6.5.4";
        return $packageJson;
    }
}

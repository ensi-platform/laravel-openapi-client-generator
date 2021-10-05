<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

class NpmPackagePatcher extends PackageManifestPatcher {
    CONST NESTJS_COMMON_PACKAGE_VERSION = '7.0.0';
    CONST NESTJS_CONFIG_PACKAGE_VERSION = '0.5.0';
    CONST RXJS_PACKAGE_VERSION = '6.5.4';
    CONST NODE_FETCH_PACKAGE_VERSION = '2.6.1';

    /**
     * @var string
     */
    protected $manifestName = 'package.json';

    /**
     * @var boolean
     */
    protected $needNestJSDependencies;

    public function __construct(string $packageRootDir, string $needNestJSDependencies)
    {
        parent::__construct($packageRootDir);
        $this->needNestJSDependencies = $needNestJSDependencies;
    }

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
        if ($this->needNestJSDependencies) {
            $packageJson = $this->addDependenciesForNestJS($packageJson);
        }
        return $packageJson;
    }

    private function addDependenciesForNestJS($packageJson) {
        $packageJson['devDependencies']['@nestjs/common'] = NpmPackagePatcher::NESTJS_COMMON_PACKAGE_VERSION;
        $packageJson['devDependencies']['@nestjs/config'] = NpmPackagePatcher::NESTJS_CONFIG_PACKAGE_VERSION;
        $packageJson['devDependencies']['rxjs'] = NpmPackagePatcher::RXJS_PACKAGE_VERSION;
        $packageJson['devDependencies']['node-fetch'] = NpmPackagePatcher::NODE_FETCH_PACKAGE_VERSION;
        $packageJson['devDependencies']['iterare'] = '1.2.1';
        $packageJson['devDependencies']['reflect-metadata'] = '0.1.13';
        return $packageJson;
    }

    private function patchLicense(array $manifest): array
    {
        $manifest['license'] = 'MIT';

        return $manifest;
    }
}

<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

class NpmPackagePatcher extends PackageManifestPatcher
{
    public const NESTJS_COMMON_PACKAGE_VERSION = '7.0.0';
    public const NESTJS_CONFIG_PACKAGE_VERSION = '0.5.0';
    public const RXJS_PACKAGE_VERSION = '6.5.4';
    public const NODE_FETCH_PACKAGE_VERSION = '2.6.1';

    protected string $manifestName = 'package.json';

    public function __construct(string $packageRootDir, protected bool $needNestJSDependencies)
    {
        parent::__construct($packageRootDir);
    }

    protected function applyPatchers(array $packageJson): array
    {
        $packageJson = $this->patchScripts($packageJson);
        $packageJson = $this->patchLicense($packageJson);
        $packageJson = $this->patchDependencies($packageJson);

        return $packageJson;
    }

    private function patchScripts(array $packageJson): array
    {
        $packageJson['scripts']['prepare'] = 'npm run build';

        return $packageJson;
    }

    private function patchDependencies(array $packageJson): array
    {
        if ($this->needNestJSDependencies) {
            $packageJson = $this->addDependenciesForNestJS($packageJson);
        }

        return $packageJson;
    }

    private function addDependenciesForNestJS(array $packageJson): array
    {
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

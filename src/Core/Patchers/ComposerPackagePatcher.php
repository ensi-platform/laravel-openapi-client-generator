<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

class ComposerPackagePatcher extends PackageManifestPatcher
{

    protected string $manifestName = 'composer.json';
    protected string $packageName;

    public function __construct(string $packageRootDir, string $packageName)
    {
        parent::__construct($packageRootDir);
        $this->packageName = $packageName;
    }

    protected function applyPatchers($manifest): array
    {
        return $this->patchPackageName($manifest);
    }

    protected function patchPackageName($manifest)
    {
        $manifest['name'] = $this->packageName;

        return $manifest;
    }
}

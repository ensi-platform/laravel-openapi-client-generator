<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

class ComposerPackagePatcher extends PackageManifestPatcher
{
    protected string $manifestName = 'composer.json';

    private bool $disableRequirePatching = false;

    public function __construct(string $packageRootDir, protected string $packageName)
    {
        parent::__construct($packageRootDir);
    }

    public function setDisableRequirePatching(bool $disableRequirePatching): self
    {
        $this->disableRequirePatching = $disableRequirePatching;

        return $this;
    }

    protected function applyPatchers(array $manifest): array
    {
        $manifest = $this->patchPackageName($manifest);

        if (false === $this->disableRequirePatching) {
            $manifest = $this->patchRequire($manifest);
        }

        return $manifest;
    }

    protected function patchPackageName(array $manifest): array
    {
        $manifest['name'] = $this->packageName;

        return $manifest;
    }

    protected function patchRequire(array $manifest): array
    {
        $manifest['require']['php'] = '^7.1 || ^8.0';
        $manifest['require']['guzzlehttp/guzzle'] = '^6.2 || ^7.0';
        $manifest['require']['guzzlehttp/psr7'] = '^1.6.1';

        return $manifest;
    }
}

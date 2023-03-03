<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

class ComposerPackagePatcher extends PackageManifestPatcher
{
    /** @var string */
    protected $manifestName = 'composer.json';
    /** @var string */
    protected $packageName;
    /** @var bool */
    private $disableRequirePatching = false;

    public function __construct(string $packageRootDir, string $packageName)
    {
        parent::__construct($packageRootDir);
        $this->packageName = $packageName;
    }

    public function setDisableRequirePatching(bool $disableRequirePatching): self
    {
        $this->disableRequirePatching = $disableRequirePatching;

        return $this;
    }

    protected function applyPatchers($manifest): array
    {
        $manifest = $this->patchPackageName($manifest);

        if (false === $this->disableRequirePatching) {
            $manifest = $this->patchRequire($manifest);
        }

        return $manifest;
    }

    protected function patchPackageName($manifest)
    {
        $manifest['name'] = $this->packageName;

        return $manifest;
    }

    protected function patchRequire($manifest)
    {
        $manifest['require']['php'] = '^7.1 || ^8.0';
        $manifest['require']['guzzlehttp/guzzle'] = '^6.2 || ^7.0';
        $manifest['require']['guzzlehttp/psr7'] = '^1.6.1';

        return $manifest;
    }
}

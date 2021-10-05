<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

abstract class PackageManifestPatcher {

    /**
     * @var string
     */
    protected $manifestName;

    /**
     * @var string
     */
    protected $packageRootDir;

    public function __construct(string $packageRootDir)
    {
        $this->packageRootDir = $packageRootDir;
    }

    public function patch(): void {
        $manifest = $this->getManifest();

        $manifest = $this->applyPatchers($manifest);

        $this->saveManifest($manifest);
    }

    abstract protected function applyPatchers($manifest);

    private function getManifest()
    {
        return json_decode(file_get_contents($this->getPackageManifestPath()), true);
    }

    private function saveManifest($packageJson)
    {
        return file_put_contents(
            $this->getPackageManifestPath(),
            json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function getPackageManifestPath(): string
    {
        return $this->packageRootDir . DIRECTORY_SEPARATOR . $this->manifestName;
    }

}

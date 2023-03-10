<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

abstract class PackageManifestPatcher
{
    protected string $manifestName;

    public function __construct(protected string $packageRootDir)
    {
    }

    public function patch(): void
    {
        $manifest = $this->getManifest();

        $manifest = $this->applyPatchers($manifest);

        $this->saveManifest($manifest);
    }

    abstract protected function applyPatchers(array $manifest): array;

    private function getManifest(): array
    {
        return json_decode(file_get_contents($this->getPackageManifestPath()), true);
    }

    private function saveManifest(array $packageJson): void
    {
        file_put_contents(
            $this->getPackageManifestPath(),
            json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function getPackageManifestPath(): string
    {
        return $this->packageRootDir . DIRECTORY_SEPARATOR . $this->manifestName;
    }
}

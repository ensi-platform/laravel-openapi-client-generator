<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

class NpmPackagePatcher {

    /**
     * @var string
     */
    protected $packageRootDir;

    public function __construct(string $packageRootDir)
    {
        $this->packageRootDir = $packageRootDir;
    }

    public function patch(): void {
        $packageJson = $this->getPackageJson();

        $packageJson = $this->patchScripts($packageJson);

        $this->savePackageJson($packageJson);
    }

    private function getPackageJson()
    {
        return json_decode(file_get_contents($this->getPackageJsonPath()), true);
    }

    private function savePackageJson($packageJson)
    {
        return file_put_contents(
            $this->getPackageJsonPath(),
            json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    private function getPackageJsonPath(): string
    {
        return $this->packageRootDir . DIRECTORY_SEPARATOR . 'package.json';
    }

    private function patchScripts($packageJson)
    {
        $packageJson['scripts']['prepare'] = 'npm run build';
        return $packageJson;
    }
}

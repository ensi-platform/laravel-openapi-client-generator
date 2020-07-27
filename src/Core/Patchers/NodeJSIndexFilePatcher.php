<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

class NodeJSIndexFilePatcher {

    /**
     * @var string
     */
    protected $packageDir;

    /**
     * @var string
     */
    protected $modelPackage;

    public function __construct(string $packageDir, string $modelPackage)
    {
        $this->packageDir = $packageDir;
        $this->modelPackage = $modelPackage;
    }

    public function patch(): void
    {
        $indexFile = $this->packageDir . DIRECTORY_SEPARATOR . 'index.ts';

        $content = file_get_contents($indexFile);
        $content .= "export * from \"./$this->modelPackage\";\n";

        file_put_contents($indexFile, $content);
    }
}

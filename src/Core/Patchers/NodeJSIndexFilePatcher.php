<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Patchers;

class NodeJSIndexFilePatcher {
    /**
     * @var string
     */
    protected $sourceDir;

    /**
     * @var bool
     */
    protected $needExportNestModule;

    public function __construct(string $sourceDir, bool $needExportNestModule)
    {
        $this->sourceDir = $sourceDir;
        $this->needExportNestModule = $needExportNestModule;
    }

    public function patch(): void
    {
        $indexFile = $this->sourceDir . DIRECTORY_SEPARATOR . 'index.ts';

        $content = file_get_contents($indexFile);
        $content .= "export * from './utils';\n";

        if ($this->needExportNestModule) {
            $content .= "export * from './module';\n";
        }

        file_put_contents($indexFile, $content);
    }
}

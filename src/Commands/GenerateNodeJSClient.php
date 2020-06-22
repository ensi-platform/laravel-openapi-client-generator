<?php

namespace Greensight\LaravelOpenapiClientGenerator\Commands;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

use Greensight\LaravelOpenapiClientGenerator\Core\Patchers\NodeJSEnumPatcher;

class GenerateNodeJSClient extends GenerateClient {
    /**
     * @var string
     */
    protected $signature = 'openapi:generate-client-nodejs';

    /**
     * @var string
     */
    protected $description = 'Generate nodejs http client from openapi spec files by OpenApi Generator';

    /**
     * @var string
     */
    protected $client = 'js';

    /**
     * @var string
     */
    protected $generator = 'typescript-axios';

    public function __construct()
    {
        parent::__construct();
    }

    protected function patchClientPackage(): void
    {
        $this->patchEnums();
    }

    private function patchEnums(): void
    {
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->outputDir,
                    FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
                )
            ),
            '/-enum\.ts$/i',
            RegexIterator::MATCH
        );

        foreach ($files as $file) {
            $this->info("Patch enum: $file");

            $patcher = new NodeJSEnumPatcher($file, $this->apidocDir);
            $patcher->patch();
        }
    }
}

<?php

namespace Ensi\LaravelOpenapiClientGenerator\Commands;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\NodeJSEnumPatcher;
use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\NpmPackagePatcher;
use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\TypeScriptConfigPatcher;
use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\NodeJSIndexFilePatcher;

use Ensi\LaravelOpenapiClientGenerator\Core\Generators\NestModuleGenerator;
use Ensi\LaravelOpenapiClientGenerator\Core\Generators\NodeJSUtilsGenerator;

class GenerateNodeJSClient extends GenerateClient
{
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
    protected $generator = 'typescript-fetch';

    /**
     * @var string
     */
    protected $needGenerateNestJSModule;

    public function __construct()
    {
        parent::__construct();
        $this->needGenerateNestJSModule = config('openapi-client-generator.js_args.generate_nestjs_module');
    }

    protected function patchClientPackage(): void
    {
        $this->patchEnums();
        $this->patchNpmPackage();
        $this->patchTypeScriptConfig();
        $this->generateNodeJSUtils();
        $this->generateNestJSModule();
        $this->patchIndexFile();
    }

    private function patchEnums(): void
    {
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->getModelsDir(),
                    FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
                )
            ),
            '/Enum\.ts$/i',
            RegexIterator::MATCH
        );

        foreach ($files as $file) {
            $this->info("Patch enum: $file");

            $patcher = new NodeJSEnumPatcher($file, $this->apidocDir);
            $patcher->patch();
        }
    }

    private function patchIndexFile(): void
    {
        $patcher = new NodeJSIndexFilePatcher($this->getSourceDir(), $this->needGenerateNestJSModule);
        $patcher->patch();
    }

    private function patchNpmPackage(): void
    {
        $patcher = new NpmPackagePatcher($this->outputDir, $this->needGenerateNestJSModule);
        $patcher->patch();
    }

    private function patchTypeScriptConfig(): void
    {
        $patcher = new TypeScriptConfigPatcher($this->outputDir);
        $patcher->patch();
    }

    private function generateNestJSModule(): void
    {
        if ($this->needGenerateNestJSModule) {
            $generator = new NestModuleGenerator(
                $this->getSourceDir(),
                $this->params['npmName'],
                $this->getApisDir()
            );
            $generator->generate();
        }
    }

    private function generateNodeJSUtils(): void
    {
        $generator = new NodeJSUtilsGenerator($this->getSourceDir());
        $generator->generate();
    }

    private function getSourceDir(): string {
        return $this->outputDir . DIRECTORY_SEPARATOR . 'src';
    }

    private function getModelsDir(): string {
        return $this->getSourceDir() . DIRECTORY_SEPARATOR . 'models';
    }

    private function getApisDir(): string {
        return $this->getSourceDir() . DIRECTORY_SEPARATOR . 'apis';
    }
}

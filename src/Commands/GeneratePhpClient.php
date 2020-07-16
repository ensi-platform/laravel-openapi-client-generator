<?php

namespace Greensight\LaravelOpenapiClientGenerator\Commands;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

use Greensight\LaravelOpenapiClientGenerator\Core\Patchers\PhpEnumPatcher;
use Greensight\LaravelOpenapiClientGenerator\Core\Patchers\ComposerPackagePatcher;
use Greensight\LaravelOpenapiClientGenerator\Core\Generators\LaravelServiceProviderGenerator;

class GeneratePhpClient extends GenerateClient {
    /**
     * @var string
     */
    protected $signature = 'openapi:generate-client-php';

    /**
     * @var string
     */
    protected $description = 'Generate php http client from openapi spec files by OpenApi Generator';

     /**
     * @var string
     */
    protected $client = 'php';

    /**
     * @var string
     */
    protected $generator = 'php';

    public function __construct()
    {
        parent::__construct();
    }

    protected function patchClientPackage(): void
    {
        $this->patchEnums();
        $this->patchComposerPackage();
        $this->generateLaravelServiceProvider();
    }

    private function patchEnums(): void
    {
        $files = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $this->outputDir . DIRECTORY_SEPARATOR . 'lib',
                    FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
                )
            ),
            '/Enum\.php$/i',
            RegexIterator::MATCH
        );

        foreach ($files as $file) {
            $this->info("Patch enum: $file");

            $patcher = new PhpEnumPatcher($file, $this->apidocDir);
            $patcher->patch();
        }
    }

    private function patchComposerPackage(): void
    {
        $patcher = new ComposerPackagePatcher($this->outputDir);
        $patcher->patch();
    }

    private function generateLaravelServiceProvider(): void {
        $generator = new LaravelServiceProviderGenerator(
            $this->outputDir,
            $this->params['invokerPackage'],
            $this->params['packageName'],
            $this->params['apiPackage']
        );

        $generator->generate();
    }
}

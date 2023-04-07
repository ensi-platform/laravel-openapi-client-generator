<?php

namespace Ensi\LaravelOpenapiClientGenerator\Commands;

use Ensi\LaravelOpenapiClientGenerator\Core\Generators\PhpProviderGenerator;
use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\ComposerPackagePatcher;
use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\PhpEnumPatcher;
use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\ReadmePatcher;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class GeneratePhpClient extends GenerateClient
{
    /** @var string */
    protected $signature = 'openapi:generate-client-php';

    /** @var string */
    protected $description = 'Generate php http client from openapi spec files by OpenApi Generator';

    protected string $client = 'php';

    protected string $generator = 'php';

    protected string $composerName;

    protected string $laravelPackageConfigKey;

    private bool $disableComposerPatchRequire;

    public function __construct()
    {
        parent::__construct();

        $this->composerName = config("openapi-client-generator.{$this->client}_args.composer_name");
        $this->laravelPackageConfigKey = config(
            "openapi-client-generator.{$this->client}_args.laravel_package_config_key",
            ''
        );

        $this->disableComposerPatchRequire = (bool)config(
            "openapi-client-generator.{$this->client}_args.composer_disable_patch_require",
            false
        );
    }

    protected function patchClientPackage(): void
    {
        //        $this->patchEnums();
        $this->patchComposerPackage();
        $this->patchReadme();
        $this->generateProvider();
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
            try {
                $patcher = new PhpEnumPatcher($file);
                $patcher->patch();
            } catch (Exception $e) {
                $this->info("Patch enum: $file\t[ERROR]");
                $this->error($e->getMessage());

                continue;
            }

            $this->info("Patch enum: $file\t[OK]");
        }
    }

    private function patchComposerPackage(): void
    {
        $patcher = new ComposerPackagePatcher($this->outputDir, $this->composerName);
        $patcher
            ->setDisableRequirePatching($this->disableComposerPatchRequire)
            ->patch();
    }

    private function generateProvider(): void
    {
        $generator = new PhpProviderGenerator(
            $this->outputDir,
            $this->params['invokerPackage'],
            $this->params['packageName'],
            $this->params['apiPackage'],
            $this->params['modelPackage']
        );

        $generator->generate();
    }

    private function patchReadme(): void
    {
        $this->info('Patch README.md');

        $patcher = new ReadmePatcher($this->outputDir, $this->gitRepo, $this->gitUser, $this->composerName);
        $patcher->patch();
    }
}

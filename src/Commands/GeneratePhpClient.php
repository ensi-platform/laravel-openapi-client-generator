<?php

namespace Ensi\LaravelOpenapiClientGenerator\Commands;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\PhpEnumPatcher;
use Ensi\LaravelOpenapiClientGenerator\Core\Patchers\ComposerPackagePatcher;
use Ensi\LaravelOpenapiClientGenerator\Core\Generators\PhpProviderGenerator;

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

    /**
     * @var string
     */
    protected $composerName;

    /**
     * @var string
     */
    protected $laravelPackageConfigKey;

    /** @var bool */
    private $disableComposerPatchRequire;

    public function __construct()
    {
        parent::__construct();
        $this->composerName = config('openapi-client-generator.php_args.composer_name');
        $this->params = array_merge($this->params, ['composerName' => $this->composerName]);
        $this->laravelPackageConfigKey = config("openapi-client-generator.{$this->client}_args.laravel_package_config_key", '');

        $this->disableComposerPatchRequire = (bool) config('openapi-client-generator.php_args.composer_disable_patch_require', false);
    }

    protected function patchClientPackage(): void
    {
        $this->patchEnums();
        $this->patchComposerPackage();
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
            $this->info("Patch enum: $file");

            $patcher = new PhpEnumPatcher($file, $this->apidocDir);
            $patcher->patch();
        }
    }

    private function patchComposerPackage(): void
    {
        $patcher = new ComposerPackagePatcher($this->outputDir, $this->composerName);
        $patcher
            ->setDisableRequirePatching($this->disableComposerPatchRequire)
            ->patch();
    }

    private function generateProvider(): void {
        $generator = new PhpProviderGenerator(
            $this->outputDir,
            $this->params['invokerPackage'],
            $this->params['packageName'],
            $this->params['apiPackage'],
            $this->params['modelPackage']
        );

        $generator->generate();
    }
}

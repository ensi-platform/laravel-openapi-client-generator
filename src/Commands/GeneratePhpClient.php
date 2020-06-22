<?php

namespace Greensight\LaravelOpenapiClientGenerator\Commands;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

use Greensight\LaravelOpenapiClientGenerator\Core\Patchers\PhpEnumPatcher;

class GeneratePhpClient extends Command {
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
    private $apidocDir;

    /**
     * @var string
     */
    private $outputDir;

    /**
     * @var string
     */
    private $gitUserId;

    /**
     * @var string
     */
    private $gitRepoId;

    /**
     * @var array
     */
    private $params;

    public function __construct()
    {
        parent::__construct();

        $this->apidocDir = config('openapi-client-generator.apidoc_dir');
        $this->outputDir = config('openapi-client-generator.output_dir') . '-php';
        $this->gitUserId = config('openapi-client-generator.php_args.git_user_id', '');
        $this->gitRepoId = config('openapi-client-generator.php_args.git_repo_id', '');
        $this->params = config('openapi-client-generator.php_args.params');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->generateClientPackage();
        $this->patchEnums();
    }

    private function generateClientPackage(): void
    {
        $bin = 'npx @openapitools/openapi-generator-cli';
        $command = "$bin generate -i $this->apidocDir/index.yaml -g php -o $this->outputDir";

        $paramsArgument = $this->getParamsArgument();

        if (Str::length($paramsArgument) > 0) {
            $command .= " -p \"$paramsArgument\"";
        }

        if (Str::length($this->gitUserId) > 0) {
            $command .= " --git-user-id $this->gitUserId";
        }

        if (Str::length($this->gitRepoId) > 0) {
            $command .= " --git-repo-id $this->gitRepoId";
        }

        $this->info("Generate client by command: $command");

        shell_exec($command);
    }

    private function getParamsArgument(): string
    {
        return collect($this->params)
            ->map(function ($value, $name) {
                $stringValue = var_export($value, true);
                return "$name=$stringValue";
            })
            ->join(',');
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
            '/Enum\.php$/i',
            RegexIterator::MATCH
        );

        foreach ($files as $file) {
            $this->info("Patch enum: " . $file->getPathName());

            $patcher = new PhpEnumPatcher($file, $this->apidocDir);
            $patcher->patch();
        }
    }
}

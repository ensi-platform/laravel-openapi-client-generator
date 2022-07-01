<?php

namespace Ensi\LaravelOpenapiClientGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

abstract class GenerateClient extends Command
{
    /**
     * @var string
     * Client name: js or php, must be set in child classes
     */
    protected $client;

    /**
     * @var string
     * Generator name, one of valid openapi generators names
     */
    protected $generator;

    /**
     * @var string
     */
    protected $apidocDir;

    /**
     * @var string
     */
    protected $outputDir;

    /**
     * @var string
     */
    protected $gitUser;

    /**
     * @var string
     */
    protected $gitRepo;

    /**
     * @var string
     */
    protected $gitHost;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $globalParams;

    /**
     * @var string
     */
    protected $templateDir;

    /**
     * @var array
     */
    protected $filesToIgnoreDuringCleanup;

    public function __construct()
    {
        parent::__construct();

        $this->apidocDir = config('openapi-client-generator.apidoc_dir');
        $this->outputDir = config('openapi-client-generator.output_dir_template') . "-$this->client";

        $this->gitUser = config('openapi-client-generator.git_user', '');
        $this->gitRepo = config('openapi-client-generator.git_repo_template', '') . "-$this->client";
        $this->gitHost = config('openapi-client-generator.git_host', '');

        $this->params = config("openapi-client-generator.{$this->client}_args.params");
        $this->globalParams = config("openapi-client-generator.{$this->client}_args.global_params", []);
        $this->templateDir = config("openapi-client-generator.{$this->client}_args.template_dir", '');
        $this->filesToIgnoreDuringCleanup = config("openapi-client-generator.{$this->client}_args.files_to_ignore_during_cleanup", []);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->recursiveClearDirectory($this->outputDir);
        if (self::FAILURE === $this->generateClientPackage()) {
            return self::FAILURE;
        }
        $this->patchClientPackage();
        $this->copyLicenseToClientPackage();

        return self::SUCCESS;
    }

    protected abstract function patchClientPackage(): void;

    private function generateClientPackage(): int
    {
        $bin = 'npx @openapitools/openapi-generator-cli';
        $i = escapeshellarg($this->apidocDir . DIRECTORY_SEPARATOR . "index.yaml");
        $g = escapeshellarg($this->generator);
        $o = escapeshellarg($this->outputDir);
        $command = "$bin generate -i $i -g $g -o $o " . $this->getGeneratorArguments();

        $this->info("Generating $this->client client by command: $command");

        $output = null;
        $resultCode = null;
        exec($command, $output, $resultCode);

        return $resultCode;
    }

    private function getGeneratorArguments(): string
    {
        $arguments = '';

        if (Str::length($this->gitUser) > 0) {
            $arguments .= " --git-user-id " . escapeshellarg($this->gitUser);
        }

        if (Str::length($this->gitRepo) > 0) {
            $arguments .= " --git-repo-id " . escapeshellarg($this->gitRepo);
        }

        if (Str::length($this->gitHost) > 0) {
            $arguments .= " --git-host " . escapeshellarg($this->gitHost);
        }

        if (Str::length($this->templateDir) > 0) {
            $arguments .= " -t " . escapeshellarg($this->templateDir);
        }

        $additionalParams = $this->getParamsArgument($this->params);
        if (Str::length($additionalParams) > 0) {
            $arguments .= " -p " . escapeshellarg($additionalParams);
        }

        $globalParams = $this->getParamsArgument($this->globalParams);
        if (Str::length($globalParams) > 0) {
            $arguments .= " --global-property=" . escapeshellarg($globalParams);
        }

        return $arguments;
    }

    private function getParamsArgument(array $params): string
    {
        return collect($params)
            ->map(function ($value, $name) {
                $escapedValue = PHP_OS_FAMILY !== 'Windows' ? str_replace("\\", "\\\\", $value) : $value;
                return "$name=$escapedValue";
            })
            ->join(',');
    }

    private function copyLicenseToClientPackage(): void
    {
        $internalSource = $this->internalTemplatePath('LICENSE-template.md');
        $externalSource = $this->externalTemplatePath('LICENSE-template.md');
        $source = !is_null($externalSource) && file_exists($externalSource) ? $externalSource : $internalSource;

        $dest = $this->outputDir . DIRECTORY_SEPARATOR . 'LICENSE.md';
        if (!file_exists($dest) && file_exists($source)) {
            copy($source, $dest);
            $this->info("Template LICENSE.md copied to package");
        }
    }

    protected function internalTemplatePath(string $path): string
    {
        return __DIR__ . '/../../templates/' . ltrim($path, '/');
    }

    protected function externalTemplatePath(string $path): ?string
    {
        $resultPath = null;

        if ($this->templateDir) {
            $resultPath = rtrim($this->templateDir, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR
                . ltrim($path, DIRECTORY_SEPARATOR);
        }

        return $resultPath;
    }

    private function recursiveClearDirectory(string $dir, int $level = 0, string $baseDir = ''): bool
    {
        if (!$dir) {
            return true;
        }

        $disableDeleteDir = false;
        foreach (scandir($dir) as $fileWithoutDir) {
            if (in_array($fileWithoutDir, ['..', '.'])) {
                continue;
            }
            $file = $dir . "/" . $fileWithoutDir;
            $pathFromBase = $baseDir ? $baseDir . '/' . $fileWithoutDir : $fileWithoutDir;

            if (in_array($pathFromBase, $this->filesToIgnoreDuringCleanup)) {
                $disableDeleteDir = true;
                continue;
            }

            if (is_dir($file)) {
                $disableDeleteDir = $this->recursiveClearDirectory($file, $level + 1, $pathFromBase) || $disableDeleteDir;
            } else {
                unlink($file);
            }
        }

        if ($level > 0 && !$disableDeleteDir) {
            rmdir($dir);
        }

        return $disableDeleteDir;
    }
}

<?php

namespace Ensi\LaravelOpenapiClientGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

abstract class GenerateClient extends Command
{
    /** Client name: js or php, must be set in child classes */
    protected string $client;

    /** Generator name, one of valid openapi generators names */
    protected string $generator;
    protected string $apiDocDir;
    protected bool $asyncApiEnabled;
    protected string $asyncApiOutputDir;
    protected string $asyncApiDocDir;
    protected string $asyncApiTemplateDir;
    protected string $asyncApiPackage;
    protected string $outputDir;
    protected string $gitUser;
    protected string $gitRepo;
    protected string $gitHost;
    protected array $params;
    protected string $templateDir;
    protected array $filesToIgnoreDuringCleanup;

    public function __construct()
    {
        parent::__construct();

        $this->apiDocDir = config('openapi-client-generator.apidoc_dir');
        $this->outputDir = config('openapi-client-generator.output_dir_template') . "-$this->client";

        $this->asyncApiEnabled = config('openapi-client-generator.async_api.enabled', false);
        $this->asyncApiTemplateDir = config('openapi-client-generator.async_api.template_dir', $this->getDefaultAsyncApiTemplateDir());
        $this->asyncApiDocDir = config('openapi-client-generator.async_api.doc_dir', $this->getDefaultAsyncApiDocDir());
        $this->asyncApiOutputDir = config('openapi-client-generator.async_api.output_dir', "async");
        $this->asyncApiPackage = config('openapi-client-generator.async_api.package', "AsyncAPI");

        $this->gitUser = config('openapi-client-generator.git_user', '');
        $this->gitRepo = config('openapi-client-generator.git_repo_template', '') . "-$this->client";
        $this->gitHost = config('openapi-client-generator.git_host', '');

        $this->params = config("openapi-client-generator.{$this->client}_args.params");
        $this->templateDir = config("openapi-client-generator.{$this->client}_args.template_dir", '');
        $this->filesToIgnoreDuringCleanup = config("openapi-client-generator.{$this->client}_args.files_to_ignore_during_cleanup", []);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->recursiveClearDirectory($this->outputDir);
        if ($this->generateOpenApiClient() === self::FAILURE) {
            return self::FAILURE;
        }
        if ($this->generateAsyncApiClient() === self::FAILURE) {
            return self::FAILURE;
        }

        $this->patchClientPackage();
        $this->copyLicenseToClientPackage();

        return self::SUCCESS;
    }

    abstract protected function patchClientPackage(): void;

    private function generateOpenApiClient(): int
    {
        $bin = 'npx --yes @openapitools/openapi-generator-cli';
        $i = escapeshellarg($this->apiDocDir . DIRECTORY_SEPARATOR . "index.yaml");
        $g = escapeshellarg($this->generator);
        $o = escapeshellarg($this->outputDir);
        $command = "$bin generate -i $i -g $g -o $o " . $this->getGeneratorArguments();

        $this->info("Generating $this->client client by command: $command");

        $output = null;
        $resultCode = null;
        exec($command, $output, $resultCode);

        return $resultCode;
    }

    private function generateAsyncApiClient(): int
    {
        if (!$this->asyncApiEnabled) {
            $this->info("Generating AsyncApi skipped");

            return self::SUCCESS;
        }

        $path = implode(DIRECTORY_SEPARATOR, [$this->asyncApiTemplateDir, "generate.js"]);
        $output = $this->outputDir . DIRECTORY_SEPARATOR . $this->asyncApiOutputDir;

        $o = escapeshellarg($output);
        $f = escapeshellarg($this->asyncApiDocDir . DIRECTORY_SEPARATOR . "index.yaml");
        $n = escapeshellarg($this->asyncApiPackage);
        $command = "node $path -o $o -f $f -n $n";

        $this->info("Generating AsyncApi $this->client client by command: $command");

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

        $additionalParams = $this->getAdditionalParamsArgument();

        if (Str::length($additionalParams) > 0) {
            $arguments .= " -p " . escapeshellarg($additionalParams);
        }

        return $arguments;
    }

    private function getAdditionalParamsArgument(): string
    {
        return collect($this->params)
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
        if (!file_exists($dest) && file_exists($source) && filesize($source) > 0) {
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

        if ($level === 0 && !is_dir($dir)) {
            mkdir($dir, 0755, true);
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

    private function getDefaultAsyncApiTemplateDir(): string
    {
        return realpath(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "..", "templates", "AsyncAPI"]));
    }

    private function getDefaultAsyncApiDocDir(): string
    {
        return realpath(implode(DIRECTORY_SEPARATOR, ["", "var", "www", "public", "async-docs", "v1"]));
    }
}

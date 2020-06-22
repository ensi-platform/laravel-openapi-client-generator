<?php

namespace Greensight\LaravelOpenapiClientGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

abstract class GenerateClient extends Command {
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

    public function __construct()
    {
        parent::__construct();

        $this->apidocDir = config('openapi-client-generator.apidoc_dir');
        $this->outputDir = config('openapi-client-generator.output_dir') . "-$this->client";

        $this->gitUser = config('openapi-client-generator.git_user', '');
        $this->gitRepo = config('openapi-client-generator.git_repo', '');
        $this->gitHost = config('openapi-client-generator.git_host', '');

        $this->params = config("openapi-client-generator.{$this->client}_args.params");
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->generateClientPackage();
        $this->patchClientPackage();
    }

    private function generateClientPackage(): void
    {
        $bin = 'npx @openapitools/openapi-generator-cli';
        $command = "$bin generate -i $this->apidocDir/index.yaml -g $this->generator -o $this->outputDir " . $this->getGeneratorArguments();

        $this->info("Generate $this->client client by command: $command");

        shell_exec($command);
    }

    private function getGeneratorArguments(): string
    {
        $arguments = '';

        if (Str::length($this->gitUser) > 0) {
            $arguments .= " --git-user-id $this->gitUser";
        }

        if (Str::length($this->gitRepo) > 0) {
            $arguments .= " --git-repo-id $this->gitRepo";
        }

        if (Str::length($this->gitHost) > 0) {
            $arguments .= " --git-host $this->gitHost";
        }

        $additionalParams = $this->getAdditionalParamsArgument();

        if (Str::length($additionalParams) > 0) {
            $arguments .= " -p \"$additionalParams\"";
        }

        return $arguments;
    }

    private function getAdditionalParamsArgument(): string
    {
        return collect($this->params)
            ->map(function ($value, $name) {
                $stringValue = var_export($value, true);
                return "$name=$stringValue";
            })
            ->join(',');
    }

    protected abstract function patchClientPackage(): void;
}

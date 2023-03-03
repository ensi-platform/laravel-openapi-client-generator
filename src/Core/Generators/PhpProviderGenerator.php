<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Generators;

use FilesystemIterator;
use Illuminate\Support\Collection;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

class PhpProviderGenerator
{
    public function __construct(
        private readonly string $packageDir,
        private readonly string $namespace,
        private readonly string $packageName,
        private readonly string $apiPackage,
        private readonly string $modelPackage,
    ) {
    }

    public function generate(): void
    {
        $file = $this->getProviderFile();

        $this->saveProviderFile($file);
    }

    private function getClassesFromDirectory(string $directory): Collection
    {
        $apis = new FilesystemIterator(
            $directory,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
        );

        return collect($apis)->map(function ($service) {
            return $service->getBasename('.php');
        });
    }

    private function getProviderFile(): PhpFile
    {
        $file = new PhpFile();
        $namespace = $file->addNamespace($this->namespace);
        $class = $namespace->addClass($this->getProviderName());

        $this->addApisToProviderClass($class);
        $this->addDtosToProviderClass($class);
        $this->addConfigurationToProviderClass($class);

        return $file;
    }

    private function addApisToProviderClass($class): void
    {
        $apis = $this->getClassesFromDirectory(
            $this->packageDir . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $this->apiPackage
        );
        $apiClassStrings = $apis->map(function ($className) {
            return "\\$this->namespace\\$this->apiPackage\\$className";
        });

        $class->addProperty('apis', $apiClassStrings->values()->all())
            ->setPublic()
            ->setStatic()
            ->addComment('@var string[]');
    }

    private function addDtosToProviderClass($class): void
    {
        $dtos = $this->getClassesFromDirectory(
            $this->packageDir . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $this->modelPackage
        );
        $dtoClassStrings = $dtos->map(function ($className) {
            return "\\$this->namespace\\$this->modelPackage\\$className";
        });

        $class->addProperty('dtos', $dtoClassStrings->values()->all())
            ->setPublic()
            ->setStatic()
            ->addComment('@var string[]');
    }

    private function addConfigurationToProviderClass($class): void
    {
        $class->addProperty('configuration', "\\$this->namespace\\Configuration")
            ->setPublic()
            ->setStatic()
            ->addComment('@var string');
    }

    private function saveProviderFile($file): void
    {
        $printer = new PsrPrinter();
        $serviceProviderName = $this->getProviderName();
        file_put_contents(
            $this->packageDir . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $serviceProviderName . ".php",
            $printer->printFile($file)
        );
    }

    private function getProviderName(): string
    {
        return "{$this->packageName}Provider";
    }
}

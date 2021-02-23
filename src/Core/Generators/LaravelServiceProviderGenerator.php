<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Generators;

use FilesystemIterator;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

class LaravelServiceProviderGenerator {
    /**
     * @var string
     */
    private $packageDir;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $apiPackage;

    /**
     * @var string
     */
    private $laravelPackageConfigKey;

    public function __construct(string $packageDir, string $namespace, string $packageName, string $apiPackage, string $laravelPackageConfigKey)
    {
        $this->packageDir = $packageDir;
        $this->namespace = $namespace;
        $this->packageName = $packageName;
        $this->apiPackage = $apiPackage;
        $this->laravelPackageConfigKey = $laravelPackageConfigKey;
    }

    public function generate(): void
    {
        $services = $this->getServices();

        $file = $this->getServiceProviderFile($services);

        $this->saveServiceProviderFile($file);
    }

    private function getServices()
    {
        $services = new FilesystemIterator(
            $this->packageDir . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $this->apiPackage,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
        );

        return collect($services)->map(function ($service) {
            return $service->getBasename('.php');
        });
    }

    private function getServiceProviderFile($services)
    {
        $file = new PhpFile();
        $namespace = $file->addNamespace($this->namespace);
        $this->addUse($namespace, $services);

        $class = $namespace->addClass($this->getServiceProviderName());
        $class->setExtends('Illuminate\Support\ServiceProvider');

        $this->addRegisterMethod($class, $services);
        $this->addBootMethod($class, $services);

        return $file;
    }

    private function saveServiceProviderFile($file)
    {
        $printer = new PsrPrinter();
        $serviceProviderName = $this->getServiceProviderName();
        file_put_contents($this->packageDir . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $serviceProviderName . ".php", $printer->printFile($file));
    }

    private function addUse($namespace, $services): void
    {
        $namespace->addUse('GuzzleHttp\Client');

        foreach ($services as $service) {
            $namespace->addUse("$this->namespace\\$this->apiPackage\\$service");
        }
    }

    private function addBootMethod($class): void
    {
        $class->addMethod('boot');
    }

    private function addRegisterMethod($class, $services): void
    {
        $config = $this->laravelPackageConfigKey;

        $body = "\$client = new Client(config('$config'));";
        $body .= "\n\n\$config = new Configuration();";
        $body .= "\n\$config->setHost(config('$config.base_uri'));\n";

        foreach ($services as $service) {
            $body .= "\n\$this->app->instance($service::class, new $service(\$client, \$config));";
        }

        $class->addMethod('register')->setBody($body);
    }

    private function getServiceProviderName(): string
    {
        return "{$this->packageName}ServiceProvider";
    }

}

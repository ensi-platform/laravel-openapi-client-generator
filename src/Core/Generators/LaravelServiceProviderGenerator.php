<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Generators;

use FilesystemIterator;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Illuminate\Support\Str;

class LaravelServiceProviderGenerator {
    CONST CONFIG_FILENAME = 'php-client-config.php';

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

    public function __construct(string $packageDir, string $namespace, string $packageName, string $apiPackage)
    {
        $this->packageDir = $packageDir;
        $this->namespace = $namespace;
        $this->packageName = $packageName;
        $this->apiPackage = $apiPackage;
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
            "$this->packageDir/lib/$this->apiPackage",
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
        );

        return collect($services)->map(fn($service) => $service->getBasename('.php'));
    }

    private function getServiceProviderFile($services)
    {
        $file = new PhpFile();
        $namespace = $file->addNamespace($this->namespace);
        $this->addUse($namespace, $services);

        $class = $namespace->addClass($this->getServiceProviderName());
        $class->setExtends('Illuminate\Support\ServiceProvider');

        $class->addConstant('CONFIG_FILENAME', self::CONFIG_FILENAME);

        $this->addRegisterMethod($class, $services);
        $this->addBooMethod($class, $services);

        return $file;
    }

    private function saveServiceProviderFile($file)
    {
        $printer = new PsrPrinter();
        $serviceProviderName = $this->getServiceProviderName();
        file_put_contents("$this->packageDir/lib/$serviceProviderName.php", $printer->printFile($file));
    }

    private function addUse($namespace, $services): void
    {
        $namespace->addUse('GuzzleHttp\Client');

        foreach ($services as $service) {
            $namespace->addUse("$this->namespace\\$this->apiPackage\\$service");
        }
    }

    private function addBooMethod($class): void
    {
        $class->addMethod('boot');
    }

    private function addRegisterMethod($class, $services): void
    {
        $config = $this->camelCaseToKebab($this->packageName);

        $body = "\$this->publishes([ __DIR__ . '../../../config/' . self::CONFIG_FILENAME => config_path('$config.php') ]);";
        $body .= "\n\n\$client = new Client(config('$config'));";
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

    private function camelCaseToKebab(string $string): string
    {
        return Str::of(Str::lower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '-$0', $string)))->ltrim('-');
    }
}

<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Generators;

use FilesystemIterator;
use Illuminate\Support\Str;

class NestModuleGenerator
{
    CONST MODULE_DIRNAME = 'module';
    CONST MODULE_FILENAME = 'module.ts';
    CONST CONFIG_FILENAME = 'config.ts';
    CONST INDEX_FILENAME = 'index.ts';

    /**
     * @var string
     */
    private $sourceDir;

    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $apisDir;

    public function __construct(string $sourceDir, string $packageName, string $apisDir)
    {
        $this->sourceDir = $sourceDir;
        $this->packageName = $packageName;
        $this->apisDir = $apisDir;
    }

    public function generate(): void
    {
        $this->createModuleDir();
        $this->generateModuleFile();
        $this->generateConfigFile();
        $this->generateIndexFile();
    }

    private function createModuleDir(): void
    {
        if (!is_dir($this->getPathToModuleDir())) {
            mkdir($this->getPathToModuleDir());
        }
    }

    private function generateModuleFile(): void
    {
        $content = $this->getModuleFileImports();
        $content .= $this->getModuleFileClassCode();

        file_put_contents($this->getPathToModuleFile(self::MODULE_FILENAME), $content);
    }

    private function generateConfigFile(): void
    {
        $generator = new NestModuleConfigGenerator($this->getPathToModuleDir(), $this->packageName);
        $generator->generate();
    }

    private function generateIndexFile(): void
    {
        $content = collect([ self::CONFIG_FILENAME, self::MODULE_FILENAME ])
            ->map(function (string $file) {
                $name = basename($file, '.ts');
                return "export * from './$name';";
            })
            ->join("\n");
        file_put_contents($this->getPathToModuleFile(self::INDEX_FILENAME), $content);
    }

    private function getPathToModuleDir(): string
    {
        return $this->sourceDir . DIRECTORY_SEPARATOR . self::MODULE_DIRNAME;
    }

    private function getPathToModuleFile(string $moduleFile): string
    {
        return $this->getPathToModuleDir() . DIRECTORY_SEPARATOR . $moduleFile;
    }

    private function getConfigName(): string
    {
        return Str::of($this->packageName)->split('/\//')->last();
    }

    private function getClassName(): string
    {
        return Str::ucfirst(Str::of($this->getConfigName())->camel());
    }

    private function getServices()
    {
        $services = new FilesystemIterator(
            $this->apisDir,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
        );

        return collect($services)
            ->map(function ($service) { 
                return Str::ucfirst(Str::of($service->getBasename('.ts'))->camel()); 
            })
            ->filter(function ($service) { 
                return $service !== 'Index'; 
            });
    }

    private function getModuleFileImports(): string
    {
        $services = $this->getServices();
        $className = $this->getClassName();

        $servicesImport = $services->join(', ');

        $configImport = basename(self::CONFIG_FILENAME, '.ts');
        $apisImport = basename($this->apisDir);

        return <<<EOD
        import { Module } from '@nestjs/common';

        import { Configuration } from '../runtime';
        import { $servicesImport } from '../$apisImport';

        import { ${className}Config } from './$configImport';


        EOD;
    }

    private function getModuleFileClassCode(): string
    {
        $services = $this->getServices();
        $className = $this->getClassName();

        $code = <<<EOD
        @Module({

        EOD;

        $code .= $this->getModuleFileClassProvidersCode($services, $className);
        $code .= $this->getModuleFileClassExportsCode($services);

        $code .= <<<EOD
        })
        export class ${className}Module {};

        EOD;

        return $code;
    }

    private function getModuleFileClassProvidersCode($services, $className): string
    {
        $code = <<<EOD
            providers: [
                ${className}Config,

        EOD;

        foreach ($services as $service) {
            $code .= <<<EOD
                    {
                        provide: $service,
                        useFactory: (config: ${className}Config): $service => {
                            const configuration = new Configuration(config);
                            return new $service(configuration);
                        },
                        inject: [ ${className}Config ]
                    },

            EOD;
        }

        $code .= <<<EOD
            ],

        EOD;


        return $code;
    }

    private function getModuleFileClassExportsCode($services): string
    {
        $exports = $services->join(",\n\t\t");

        return <<<EOD
            exports: [
                $exports
            ]

        EOD;
    }
}

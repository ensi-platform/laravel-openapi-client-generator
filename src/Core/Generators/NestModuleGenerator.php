<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Generators;

use FilesystemIterator;
use Illuminate\Support\Str;

class NestModuleGenerator {
    CONST MODULE_DIRNAME = 'module';
    CONST MODULE_FILENAME = 'module.ts';
    CONST CONFIG_FILENAME = 'config.ts';
    CONST INDEX_FILENAME = 'index.ts';

    /**
     * @var string
     */
    private $packageDir;

    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $apiPackage;

    public function __construct(string $packageDir, string $packageName, string $apiPackage)
    {
        $this->packageDir = $packageDir;
        $this->packageName = $packageName;
        $this->apiPackage = $apiPackage;
    }

    public function generate(): void
    {
        $this->createModuleDir();
        $this->generateModuleFile();
        $this->generateConfigFile();
        $this->generateIndexFile();
        $this->patchMainIndexFile();
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
        $configName = $this->getConfigName();
        $className = $this->getClassName();
        $content = <<<EOD
        import { Injectable } from '@nestjs/common';
        import { ConfigService } from '@nestjs/config';

        import { AxiosRequestConfig } from 'axios';

        @Injectable()
        export class ${className}Config {
            constructor(private readonly configService: ConfigService) {}

            get uri(): string {
                return this.configService.get('${configName}.uri');
            }

            get options(): AxiosRequestConfig | undefined {
                return this.configService.get('${configName}.options', undefined);
            }
        }

        EOD;

        file_put_contents($this->getPathToModuleFile(self::CONFIG_FILENAME), $content);
    }

    private function generateIndexFile(): void
    {
        $content = collect([ self::CONFIG_FILENAME, self::MODULE_FILENAME ])
            ->map(function (string $file) {
                $name = basename($file, '.ts');
                return "export * from \"./$name\";";
            })
            ->join("\n");
        file_put_contents($this->getPathToModuleFile(self::INDEX_FILENAME), $content);
    }

    private function patchMainIndexFile(): void
    {
        $moduleImport = self::MODULE_DIRNAME;
        $indexFile = $this->packageDir . DIRECTORY_SEPARATOR . self::INDEX_FILENAME;

        $content = file_get_contents($indexFile);
        $content .= "export * from \"./$moduleImport\";\n";
        file_put_contents($indexFile, $content);
    }

    private function getPathToModuleDir(): string
    {
        return $this->packageDir . DIRECTORY_SEPARATOR . self::MODULE_DIRNAME;
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
            $this->packageDir . DIRECTORY_SEPARATOR . $this->apiPackage,
            FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS
        );

        return collect($services)->map(fn($service) =>
            Str::ucfirst(Str::of($service->getBasename('.ts'))->camel())
        );
    }

    private function getModuleFileImports(): string
    {
        $services = $this->getServices();
        $className = $this->getClassName();

        $servicesImport = $services->join(', ');
        $configImport = basename(self::CONFIG_FILENAME, '.ts');

        return <<<EOD
        import { Module } from '@nestjs/common';

        import { $servicesImport } from "../$this->apiPackage";

        import { ${className}Config } from "./$configImport";


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
                            return new $service({
                                basePath: config.uri,
                                baseOptions: config.options
                            });
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

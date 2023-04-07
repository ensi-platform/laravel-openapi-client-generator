<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Generators;

use Illuminate\Support\Str;

class NestModuleConfigGenerator
{
    public const MODULE_DIRNAME = 'module';
    public const MODULE_FILENAME = 'module.ts';
    public const CONFIG_FILENAME = 'config.ts';
    public const INDEX_FILENAME = 'index.ts';

    public function __construct(
        private readonly string $moduleDir,
        private readonly string $packageName,
    ) {
    }

    public function generate(): void
    {
        $this->generateConfigFile();
    }

    private function generateConfigFile(): void
    {
        $configName = $this->getConfigName();
        $className = $this->getClassName();
        $content = <<<EOD
        import { Injectable, HttpException } from '@nestjs/common';
        import { ConfigService } from '@nestjs/config';

        import fetch from 'node-fetch';

        import {
            ConfigurationParameters,
            FetchAPI,
            Middleware,
            HTTPQuery,
            HTTPHeaders,
            querystring
        } from '../runtime';

        import {
            querystringFilterWrapper
        } from '../utils';

        @Injectable()
        export class ${className}Config implements ConfigurationParameters {
            constructor(private readonly configService: ConfigService) {}

            get basePath(): string {
                return this.configService.get('${configName}.basePath');
            }

            get fetchApi(): FetchAPI | undefined {
                return this.configService.get('${configName}.fetchApi', fetch);
            };

            get middleware(): Middleware[] {
                const middlewares = this.configService.get('${configName}.middleware', []);

                middlewares.push({
                    post: async context => {
                        const { response } = context;

                        if (response.status >= 200 && response.status < 300) {
                            return Promise.resolve(response);
                        } else {
                            const result = await response.json();
                            throw new HttpException(result, response.status);
                        }
                    }
                });

                return middlewares;
            };

            get queryParamsStringify(): (params: HTTPQuery) => string | undefined {
                return this.configService.get('${configName}.queryParamsStringify', querystringFilterWrapper(querystring));
            };

            get username(): string | undefined {
                return this.configService.get('${configName}.username', undefined);
            };

            get password(): string | undefined {
                return this.configService.get('${configName}.password', undefined);
            };

            get apiKey(): string | ((name: string) => string) | undefined {
                return this.configService.get('${configName}.apiKey', undefined);
            };

            get accessToken(): string | ((name?: string, scopes?: string[]) => string) {
                return this.configService.get('${configName}.accessToken', undefined);
            };

            get headers(): HTTPHeaders | undefined {
                return this.configService.get('${configName}.headers', undefined);
            };

            get credentials(): RequestCredentials | undefined {
                return this.configService.get('${configName}.credentials', undefined);
            };
        }

        EOD;

        file_put_contents($this->getPathToConfigFile(self::CONFIG_FILENAME), $content);
    }

    private function getPathToConfigFile(string $moduleFile): string
    {
        return $this->moduleDir . DIRECTORY_SEPARATOR . $moduleFile;
    }

    private function getConfigName(): string
    {
        return Str::of($this->packageName)->split('/\//')->last();
    }

    private function getClassName(): string
    {
        return Str::ucfirst(Str::of($this->getConfigName())->camel());
    }
}

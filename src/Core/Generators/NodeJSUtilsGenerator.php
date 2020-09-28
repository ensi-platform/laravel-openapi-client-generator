<?php

namespace Greensight\LaravelOpenapiClientGenerator\Core\Generators;

class NodeJSUtilsGenerator {
    CONST UTILS_DIRNAME = 'utils';
    CONST QUERY_STRING_FILTER_WRAPPER_FILENAME = 'querystringFilterWrapper.ts';
    CONST INDEX_FILENAME = 'index.ts';

    /**
     * @var string
     */
    private $sourceDir;

    public function __construct(string $sourceDir)
    {
        $this->sourceDir = $sourceDir;
    }

    public function generate(): void
    {
        $this->createUtilsDir();
        $this->generateQueryStringFilterWrapperFile();
        $this->generateIndexFile();
    }

    private function createUtilsDir(): void
    {
        if (!is_dir($this->getPathToUtilsDir())) {
            mkdir($this->getPathToUtilsDir());
        }
    }

    private function generateQueryStringFilterWrapperFile(): void
    {
        $code = $this->getQueryStringFilterWrapperCode();

        file_put_contents($this->getPathToFile(self::QUERY_STRING_FILTER_WRAPPER_FILENAME), $code);
    }

    private function generateIndexFile(): void
    {
        $content = collect([ self::QUERY_STRING_FILTER_WRAPPER_FILENAME ])
            ->map(function (string $file) {
                $name = basename($file, '.ts');
                return "export * from './$name';";
            })
            ->join("\n");
        file_put_contents($this->getPathToFile(self::INDEX_FILENAME), $content);
    }

    private function getPathToUtilsDir(): string
    {
        return $this->sourceDir . DIRECTORY_SEPARATOR . self::UTILS_DIRNAME;
    }

    private function getPathToFile(string $file): string
    {
        return $this->getPathToUtilsDir() . DIRECTORY_SEPARATOR . $file;
    }

    private function getQueryStringFilterWrapperCode(): string
    {
        return <<<EOD
        import { HTTPQuery } from '../runtime';

        type QueryStringParser = (params: HTTPQuery, prefix?: string) => string;

        export function querystringFilterWrapper(querystring: QueryStringParser): QueryStringParser {
            return (params, prefix?) => {
                if (prefix === 'filter' && typeof params === 'object') {
                    return JSON.stringify(params);
                } else {
                    return querystring(params, prefix);
                }
            }
        }

        EOD;
    }
}

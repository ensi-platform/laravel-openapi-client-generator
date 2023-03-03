<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

class ReadmePatcher
{
    public function __construct(
        private readonly string $rootDir,
        private readonly string $gitRepo,
        private readonly string $gitUser,
        private readonly string $composerName
    ) {
    }

    public function patch(): void
    {
        $content = file_get_contents($this->getReadmePath());

        $content = str_replace(
            "\"{$this->gitUser}/{$this->gitRepo}\": \"*@dev\"",
            "\"{$this->composerName}\": \"*@dev\"",
            $content
        );

        file_put_contents($this->getReadmePath(), $content);
    }

    private function getReadmePath(): string
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . 'README.md';
    }
}

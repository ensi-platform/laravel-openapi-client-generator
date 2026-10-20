<?php

namespace Ensi\LaravelOpenapiClientGenerator\Core\Patchers;

class GuzzleJsonPatcher
{
    public function __construct(protected string $file)
    {
    }

    public function patch(): void
    {
        $content = file_get_contents($this->file);
        $patchedContent = str_replace(
            [
                '\\GuzzleHttp\\json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody))',
                '\\GuzzleHttp\\json_encode($formParams)',
            ],
            [
                '\\json_encode(ObjectSerializer::sanitizeForSerialization($_tempBody), JSON_THROW_ON_ERROR)',
                '\\json_encode($formParams, JSON_THROW_ON_ERROR)',
            ],
            $content
        );

        if ($patchedContent !== $content) {
            file_put_contents($this->file, $patchedContent);
        }
    }
}

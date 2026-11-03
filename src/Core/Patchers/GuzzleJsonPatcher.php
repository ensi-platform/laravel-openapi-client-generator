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
        $patchedContent = $content;

        foreach (['ObjectSerializer::sanitizeForSerialization($_tempBody)', '$formParams'] as $argument) {
            $statement = '$httpBody = \\GuzzleHttp\\json_encode(' . $argument . ');';
            $replacement = <<<'PHP'
            $httpBody = \json_encode({{argument}});
            if (\JSON_ERROR_NONE !== \json_last_error()) {
                throw new \GuzzleHttp\Exception\InvalidArgumentException('json_encode error: ' . \json_last_error_msg());
            }
            PHP;
            $replacement = str_replace('{{argument}}', $argument, $replacement);
            $patchedContent = preg_replace_callback(
                '/^([\t ]*)' . preg_quote($statement, '/') . '/m',
                static fn (array $matches): string => $matches[1] . str_replace("\n", "\n" . $matches[1], $replacement),
                $patchedContent
            );
        }

        if ($patchedContent !== $content) {
            file_put_contents($this->file, $patchedContent);
        }
    }
}

<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Messages;

use {{ params.packageName | safe }}\Messages\Listen\Payloads\{{schemaName | camelCase | upperFirst}}Payload;
use RdKafka\Message;

/**
 * @property {{schemaName | camelCase | upperFirst}}Payload $attributes
 */
class {{schemaName | camelCase | upperFirst}}EventMessage extends BaseEventMessage
{
    public static function makeFromRdKafka(Message $message): static
    {
        $classPayload = {{schemaName | camelCase | upperFirst}}Payload::class;

        return parent::makeFromRdKafka($message, $classPayload);
    }
}

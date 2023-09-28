<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Messages;

use Illuminate\Support\Fluent;
use RdKafka\Message;

/**
 * @property BasePayload $attributes
 */
abstract class BaseMessage extends Fluent
{
    public const CLASS_PAYLOAD = BasePayload::class;

    public static function makeFromRdKafka(Message $message): static
    {
        $payload = json_decode($message->payload, true);
        $payload['attributes'] = new static::CLASS_PAYLOAD($payload['attributes']);

        return new static($payload);
    }
}

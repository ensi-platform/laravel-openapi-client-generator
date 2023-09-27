<?php

namespace AsyncAPI\Messages\Listen;

use Illuminate\Support\Fluent;
use RdKafka\Message;

/**
 * @property BasePayload $attributes
 */
abstract class BaseMessage extends Fluent
{
    public static function makeFromRdKafka(Message $message, string $classPayload): static
    {
        $payload = json_decode($message->payload, true);
        $payload['attributes'] = new $classPayload($payload['attributes']);

        return new static($payload);
    }
}
<?php

namespace {{ params.packageName | safe }}\Messages\Listen;

use RdKafka\Message;

trait BaseMessageTrait
{
    public static function makeFromRdKafka(Message $message): static
    {
        return new static(json_decode($message->payload, true));
    }
}

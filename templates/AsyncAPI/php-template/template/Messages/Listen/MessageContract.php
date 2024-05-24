<?php

namespace {{ params.packageName | safe }}\Messages\Listen;

use RdKafka\Message;

{ params.packageName | safe }}\Messages\Listen\Payloads\BasePayload;

abstract class MessageContract
{
    protected BasePayload $payload;

    public static function makeFromRdKafka(Message $message): static
    {
        return new static(json_decode($message->payload, true));
    }

    public function setPayload(BasePayload $payload): static
    {
        $this->payload = $payload;

        return $this;
    }

    abstract public function getPayload(): BasePayload;
}

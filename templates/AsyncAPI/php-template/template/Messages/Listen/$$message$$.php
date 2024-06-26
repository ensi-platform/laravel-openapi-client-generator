<?php

namespace {{ params.packageName | safe }}\Messages\Listen;

{%- set payload = message.payload() %}

use {{ params.packageName | safe }}\Messages\Listen\Payloads\{{payload.uid() | camelCase | upperFirst}};
use RdKafka\Message;

class {{messageName | camelCase | upperFirst}} extends MessageContract
{
    public function __construct(array $payload)
    {
        $this->setPayload(new {{payload.uid() | camelCase | upperFirst}}($payload));
    }

    public function getPayload(): {{payload.uid() | camelCase | upperFirst}}
    {
        return $this->payload;
    }
}

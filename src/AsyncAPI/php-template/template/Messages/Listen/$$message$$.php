<?php

namespace {{ params.packageName | safe }}\Messages\Listen;

{%- set payload = message.payload() %}

use {{ params.packageName | safe }}\Messages\Listen\Payloads\{{payload.uid() | camelCase | upperFirst}};
use RdKafka\Message;

/**
* @method {{payload.uid() | camelCase | upperFirst}} getPayload()
*/
class {{messageName | camelCase | upperFirst}} extends MessageContract
{
    public function __construct(array $payload)
    {
        $this->setPayload(new {{payload.uid() | camelCase | upperFirst}}($payload));
    }
}

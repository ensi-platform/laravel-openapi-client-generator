<?php

namespace {{ params.packageName | safe }}\Messages\Listen;

{%- set payload = message.payload() %}

use {{ params.packageName | safe }}\Messages\Listen\Payloads\{{payload.uid() | camelCase | upperFirst}};
use RdKafka\Message;

/**
* @mixin {{payload.uid() | camelCase | upperFirst}}
*/
class {{messageName | camelCase | upperFirst}} extends {{payload.uid() | camelCase | upperFirst}}
{
    use BaseMessageTrait;
}

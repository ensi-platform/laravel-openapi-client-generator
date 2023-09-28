<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Messages;

use {{ params.packageName | safe }}\Messages\Listen\Payloads\{{schemaName | camelCase | upperFirst}}Payload;

/**
 * @property {{schemaName | camelCase | upperFirst}}Payload $attributes
 */
class {{schemaName | camelCase | upperFirst}}EventMessage extends BaseEventMessage
{
}

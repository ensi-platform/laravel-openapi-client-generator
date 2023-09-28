<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Messages;

use RdKafka\Message;

/**
 * @property BasePayload $attributes
 * @property string $event
 */
abstract class BaseEventMessage extends BaseMessage
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';
}

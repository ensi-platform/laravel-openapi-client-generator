<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Enums;

enum MessageEventEnum: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
}

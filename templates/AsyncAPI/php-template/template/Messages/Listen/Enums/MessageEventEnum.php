<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Enums;

enum MessageEventEnum: string
{
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';

    public static function getDescriptions(): array
    {
        return [
            self::CREATE->value => 'Создание',
            self::UPDATE->value => 'Обновление',
            self::DELETE->value => 'Удаление',
        ];
    }
}

<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Payloads;

use DateTime;

class BasePayload
{
    protected array $attributes = [];
    protected array $dates = [];
    protected array $objects = [];
    protected array $enums = [];

    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = match(true) {
                isset($this->dates[$key]) => static::toDate($value ?? null),
                isset($this->objects[$key]) => static::toObject($value ?? null, $this->objects[$key]),
                isset($this->enums[$key]) => static::toEnum($value ?? null, $this->enums[$key]),
                default => $value,
            };
        }
    }

    public static function toEnum(mixed $value, string $class): mixed
    {
        return isset($value) ? $class::tryFrom($value) : null;
    }

    public static function toObject(mixed $value, string $class): mixed
    {
        return isset($value) ? new $class($value) : null;
    }

    public static function toDate(mixed $value): ?DateTime
    {
        return isset($value) ? new DateTime($value) : null;
    }

    public function set($key, mixed $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return $default instanceof Closure ? $default() : $default;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson($options = 0): string|false
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}

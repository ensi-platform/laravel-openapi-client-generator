<?php

namespace {{ params.packageName | safe }}\Messages\Listen\Payloads;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

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

    public static function toDate(mixed $value): ?DateTimeInterface
    {
        if (!isset($value)) {
            return null;
        }

        return $value instanceof DateTimeInterface ? $value : new DateTimeImmutable($value);
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

        return $default instanceof \Closure ? $default() : $default;
    }

    public function toArray(): array
    {
        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            $attributes[$key] = static::serializeValue($value, $this->dates[$key] ?? null);
        }

        return $attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson($options = 0): string|false
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    protected static function serializeValue(mixed $value, ?string $dateFormat = null): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return static::formatDate($value, $dateFormat ?? 'date-time');
        }

        if ($value instanceof self) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(fn ($item) => static::serializeValue($item, $dateFormat), $value);
        }

        return $value;
    }

    protected static function formatDate(DateTimeInterface $value, string $format): string
    {
        if ($format === 'date') {
            return $value->format('Y-m-d');
        }

        $utcDateTime = DateTimeImmutable::createFromFormat('U.u', $value->format('U.u'), new DateTimeZone('UTC'));
        if ($utcDateTime === false) {
            $utcDateTime = new DateTimeImmutable('@' . $value->getTimestamp());
        }

        return match ($format) {
            'time' => $utcDateTime->setTimezone(new DateTimeZone('UTC'))->format('H:i:s.u\Z'),
            default => $utcDateTime->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z'),
        };
    }
}

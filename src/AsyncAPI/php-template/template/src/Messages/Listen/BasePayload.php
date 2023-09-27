<?php

namespace AsyncAPI\Messages\Listen;

use DateTime;

abstract class BasePayload
{
    /**
     * Associative array for storing property values
     */
    protected array $attributes = [];

    protected array $dates = [];

    protected array $objects = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
        $this->convertDates();
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    protected function getValue(string $key): mixed
    {
        $value = $this->attributes[$key] ?? null;
        $isObject = isset($this->objects[$key]) && isset($value);

        return $isObject ? $this->convertToObject($key) : $value;
    }

    protected function convertDates(): void
    {
        foreach ($this->dates as $key) {
            if (isset($this->attributes[$key])) {
                $this->attributes[$key] = new DateTime($this->attributes[$key]);
            }
        }
    }

    protected function convertToObject($key): mixed
    {
        $class = $this->objects[$key] ?? null;

        return $class ? new $class($this->attributes[$key]) : null;
    }
}

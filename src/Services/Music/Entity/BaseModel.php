<?php

namespace App\Services\Music\Entity;

class BaseModel
{
    protected $data = [];

    public function __get($key)
    {
        return $this->{$key} ?? null;
    }

    public static function fromRequest(array $data): self
    {
        $inc = new static();
        foreach ($data as $key => $value) {
            $setterName = 'set' . ucfirst($key);
            if(method_exists($inc, $setterName)) {
                $inc->$setterName($value);
                continue;
            }

            if(property_exists($inc, $key)) {
                $inc->{$key} = $value;
                continue;
            }

            $inc->pushData($key, $value);
        }

        return $inc;
    }

    public static function collection(array $data): array
    {
        $items = [];
        foreach ($data as $item) {
            $items[] = static::fromRequest($item);
        }

        return $items;
    }

    protected function pushData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getData(): array
    {
        return $this->data;
    }
}

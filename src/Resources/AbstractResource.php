<?php

namespace App\Resources;

abstract class AbstractResource
{
    abstract public function toArray(): array;

    public static function collection(array $data)
    {
        return array_map(function($item) {
            return (new static($item))->toArray();
        }, $data);
    }
}

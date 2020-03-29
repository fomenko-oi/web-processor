<?php

namespace App\Services\Music\Entity\Track;

use App\Services\Music\Entity\BaseModel;

class Label extends BaseModel
{
    /** @var int */
    public $id;

    /** @var string */
    public $name;
}

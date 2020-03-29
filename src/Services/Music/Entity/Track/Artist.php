<?php

namespace App\Services\Music\Entity\Track;

use App\Services\Music\Entity\BaseModel;

class Artist extends BaseModel
{
    const DEFAULT_COVER_WIDTH = 300;
    const DEFAULT_COVER_HEIGHT = 300;

    public $id;
    public $name;
    public $various;
    public $composer;
    public $cover = [];
    public $genres = [];

    public function getCoverUrl($width, $height): string
    {
        return sprintf(str_replace('%%', '%sx%s', $this->cover['uri']), $width, $height);
    }
}

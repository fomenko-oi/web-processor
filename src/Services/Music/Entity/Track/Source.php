<?php

namespace App\Services\Music\Entity\Track;

use App\Services\Music\Entity\BaseModel;

class Source extends BaseModel
{
    const TYPE_AAC = 'aac';
    const TYPE_MP3 = 'mp3';

    /** @var string */
    public $codec;

    /** @var string */
    public $bitrateInKbps;

    /** @var bool */
    public $gain;

    /** @var bool */
    public $preview;

    /** @var string */
    public $downloadInfoUrl;

    /** @var bool */
    public $direct;

    public function isAac(): bool
    {
        return $this->codec === self::TYPE_AAC;
    }
    public function isMp3(): bool
    {
        return $this->codec === self::TYPE_MP3;
    }
}

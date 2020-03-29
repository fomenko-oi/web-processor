<?php

namespace App\Services\Music\Entity\Track;

use App\Services\Music\Entity\BaseModel;

class Album extends BaseModel
{
    const DEFAULT_COVER_WIDTH = 300;
    const DEFAULT_COVER_HEIGHT = 300;

    const DEFAULT_OG_WIDTH = 300;
    const DEFAULT_OG_HEIGHT = 300;

    const TYPE_SINGLE = 'single';

    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $type;

    /** @var string */
    public $metaType;

    /** @var int */
    public $year;

    /** @var string */
    public $releaseDate;

    /** @var string */
    public $coverUri;

    /** @var string */
    public $ogImage;

    /** @var string */
    public $genre;

    /** @var bool */
    public $recent;

    /** @var bool */
    public $veryImportant;

    /** @var bool */
    public $available;

    /** @var bool */
    public $availableForMobile;

    /** @var bool */
    public $availablePartially;

    /** @var Artist[] */
    public $artists = [];

    /** @var array */
    public $labels = [];

    public $bests = [];
    public $trackPosition = [];

    public function setArtists(array $artists)
    {
        foreach ($artists as $artist) {
            $this->artists[] = Artist::fromRequest($artist);
        }
    }

    public function isSingle(): bool
    {
        return $this->type === self::TYPE_SINGLE;
    }

    public function getReleaseDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->releaseDate);
    }

    public function getCoverUrl($width, $height): string
    {
        return sprintf(str_replace('%%', '%sx%s', $this->coverUri), $width, $height);
    }

    public function getOgImage($width, $height): string
    {
        return sprintf(str_replace('%%', '%sx%s', $this->ogImage), $width, $height);
    }
}

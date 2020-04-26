<?php declare(strict_types=1);

namespace App\Services\Music\Entity\Album;

use App\Services\Music\Entity\BaseModel;
use App\Services\Music\Entity\Track\Artist;
use App\Services\Music\Entity\Track\Track;

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
    public $contentWarning;

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

    /** @var int */
    public $trackCount;

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

    /** @var array */
    public $bests = [];

    /** @var self[] */
    public $duplicates = [];

    /** @var array */
    public $prerolls = [];

    /** @var Track[] */
    public $volumes = [];

    public function setArtists(array $artists)
    {
        foreach ($artists as $artist) {
            $this->artists[] = Artist::fromRequest($artist);
        }
    }

    public function setVolumes(array $volumes)
    {
        foreach ($volumes[0] as $volume) {
            $this->volumes[] = Track::fromRequest($volume);
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

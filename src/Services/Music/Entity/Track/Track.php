<?php

namespace App\Services\Music\Entity\Track;

use App\Services\Music\Entity\BaseModel;

class Track extends BaseModel
{
    const DEFAULT_COVER_WIDTH = 300;
    const DEFAULT_COVER_HEIGHT = 300;

    const DEFAULT_OG_WIDTH = 300;
    const DEFAULT_OG_HEIGHT = 300;

    public $id;
    public $realId;
    public $title;
    public $major;
    public $durationMs;
    public $fileSize;
    public $storageDir;
    public $coverUri;
    public $ogImage;
    public $lyricsAvailable;
    public $type;

    /** @var Artist[] */
    public $artists = [];

    /** @var  */
    public $albums = [];

    public function setArtists(array $artists)
    {
        foreach ($artists as $artist) {
            $this->artists[] = Artist::fromRequest($artist);
        }
    }

    public function setAlbums(array $albums)
    {
        foreach ($albums as $album) {
            $this->albums[] = Album::fromRequest($album);
        }
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

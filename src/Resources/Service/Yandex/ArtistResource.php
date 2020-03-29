<?php

namespace App\Resources\Service\Yandex;

use App\Resources\AbstractResource;
use App\Services\Music\Entity\Track\Artist;

class ArtistResource extends AbstractResource
{
    /**
     * @var Artist
     */
    private Artist $artist;

    public function __construct(Artist $artist)
    {
        $this->artist = $artist;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->artist->id,
            'name' => $this->artist->name,
            'cover' => $this->artist->getCoverUrl(Artist::DEFAULT_COVER_WIDTH, Artist::DEFAULT_COVER_HEIGHT),
        ];
    }
}

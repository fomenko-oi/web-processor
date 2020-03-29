<?php

namespace App\Resources\Service\Yandex;

use App\Resources\AbstractResource;
use App\Services\Music\Entity\Track\Album;
use App\Services\Music\Entity\Track\Artist;
use App\Services\Music\Entity\Track\Track;

class AlbumResource extends AbstractResource
{
    /**
     * @var Album
     */
    private Album $album;

    public function __construct(Album $album)
    {
        $this->album = $album;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->album->id,
            'title' => $this->album->title,
            'type' => $this->album->type,
            'meta_type' => $this->album->metaType,
            'year' => $this->album->year,
            'release_date' => $this->album->releaseDate,
            'genre' => $this->album->genre,
            'labels' => $this->album->labels,
            'artists' => ArtistResource::collection($this->album->artists),
            'cover' => $this->album->getCoverUrl(Album::DEFAULT_COVER_WIDTH, Album::DEFAULT_COVER_HEIGHT),
            'og_image' => $this->album->getOgImage(Album::DEFAULT_OG_WIDTH, Album::DEFAULT_OG_HEIGHT),
        ];
    }
}

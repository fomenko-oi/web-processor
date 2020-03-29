<?php

namespace App\Resources\Service\Yandex;

use App\Resources\AbstractResource;
use App\Services\Music\Entity\Track\Track;

class TrackResource extends AbstractResource
{
    /**
     * @var Track
     */
    private Track $track;

    public function __construct(Track $track)
    {
        $this->track = $track;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->track->id,
            'real_id' => $this->track->realId,
            'title' => $this->track->title,
            'major' => $this->track->major,
            'duration' => $this->track->durationMs,
            'file_size' => $this->track->fileSize,
            'cover' => $this->track->getCoverUrl(Track::DEFAULT_COVER_WIDTH, Track::DEFAULT_COVER_HEIGHT),
            'og_image' => $this->track->getOgImage(Track::DEFAULT_OG_WIDTH, Track::DEFAULT_OG_HEIGHT),
            'lyrics_available' => $this->track->lyricsAvailable,
            'type' => $this->track->type,
            'artists' => ArtistResource::collection($this->track->artists),
            'albums' => AlbumResource::collection($this->track->albums),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Service\Yandex\Track\Event;

use App\Entity\Service\Yandex\Track\Id;

class TrackCreated
{
    public Id $trackId;
    public int $realTrackId;
    public int $bitrate;

    public function __construct(Id $trackId, int $realTrackId, int $bitrate)
    {
        $this->trackId = $trackId;
        $this->realTrackId = $realTrackId;
        $this->bitrate = $bitrate;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Service\Yandex\Album\Event;

use App\Entity\Service\Yandex\Album\Id;

class AlbumCreated
{
    public Id $albumId;
    public int $bitrate;

    public function __construct(Id $albumId, int $bitrate)
    {
        $this->albumId = $albumId;
        $this->bitrate = $bitrate;
    }
}

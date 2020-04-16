<?php

declare(strict_types=1);

namespace App\Resources\Service\Yandex;

use App\Resources\AbstractResource;
use App\Services\Music\Entity\Track\Lyrics;
use App\Services\Music\Entity\Track\Track;

class LyricsResource extends AbstractResource
{
    /**
     * @var Lyrics
     */
    private Lyrics $lyrics;

    public function __construct(Lyrics $lyrics)
    {
        $this->lyrics = $lyrics;
    }

    public function toArray(): array
    {
        return [
            'text' => nl2br($this->lyrics->fullLyrics),
            'lang' => $this->lyrics->textLanguage
        ];
    }
}

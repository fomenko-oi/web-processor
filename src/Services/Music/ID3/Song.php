<?php

declare(strict_types=1);

namespace App\Services\Music\ID3;

class Song
{
    /**
     * @var string
     */
    private string $title;
    /**
     * @var string
     */
    private ?string $album;
    /**
     * @var string
     */
    private ?string $artist;
    /**
     * @var int
     */
    private ?int $year;
    /**
     * @var int
     */
    private ?int $number;

    public function __construct(string $title, string $album = null, string $artist = null, int $year = null, int $number = null)
    {
        $this->title = $title;
        $this->album = $album;
        $this->artist = $artist;
        $this->year = $year;
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getAlbum(): ?string
    {
        return $this->album;
    }

    /**
     * @return string
     */
    public function getArtist(): ?string
    {
        return $this->artist;
    }

    /**
     * @return int
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * @return int
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }
}

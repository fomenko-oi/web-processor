<?php

declare(strict_types=1);

namespace App\Entity\Service\Yandex;

use App\Entity\Service\Yandex\Album\Event\AlbumCreated;
use App\Entity\Service\Yandex\Album\Id;
use App\Infrastructure\AggregateRoot;
use App\Infrastructure\EventsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="service_yandex_albums")
 */
class Album implements AggregateRoot
{
    use EventsTrait;

    const STATUS_NEW = 'new';
    const STATUS_SUCCESS = 'success';
    const STATUS_PROGRESS = 'progress';

    /**
     * @ORM\Column(type="yandex_album_id")
     * @ORM\Id()
     */
    private $id;
    /**
     * @ORM\Column(type="integer")
     */
    private $albumId;
    /**
     * @ORM\Column(type="string")
     */
    private string $name;
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;
    /**
     * @ORM\Column(type="integer")
     */
    private int $bitrate;

    /**
     * @var Track[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Service\Yandex\Track")
     * @ORM\JoinTable(
     *     name="service_yandex_albums_tracks",
     *     joinColumns={@ORM\JoinColumn(name="album_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="track_id", referencedColumnName="id")}
     *     )
     */
    private $tracks;

    public function __construct(Id $id, int $albumId, \DateTimeImmutable $createdAt, string $name, int $bitrate = 320)
    {
        $this->id = $id;
        $this->albumId = $albumId;
        $this->createdAt = $createdAt;
        $this->name = $name;
        $this->bitrate = $bitrate;
        $this->tracks = new ArrayCollection();
        $this->recordEvent(new AlbumCreated($this->id, $bitrate));
    }

    public function addTrack(Track $track): self
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks[] = $track;
        }

        return $this;
    }
    /**
     * @return Collection|Track[]
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    /**
     * @return Id
     */
    public function getId(): Id
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getBitrate(): int
    {
        return $this->bitrate;
    }

    /**
     * @param int $bitrate
     */
    public function setBitrate(int $bitrate): self
    {
        $this->bitrate = $bitrate;
        return $this;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }
}

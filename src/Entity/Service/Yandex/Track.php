<?php

declare(strict_types=1);

namespace App\Entity\Service\Yandex;

use App\Entity\Service\Yandex\Track\Event\TrackCreated;
use App\Entity\Service\Yandex\Track\Event\TrackDownloaded;
use App\Entity\Service\Yandex\Track\Id;
use App\Infrastructure\AggregateRoot;
use App\Infrastructure\EventsTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="service_yandex_tracks")
 */
class Track implements AggregateRoot
{
    use EventsTrait;

    const STATUS_NEW = 'new';
    const STATUS_SUCCESS = 'success';
    const STATUS_PROGRESS = 'progress';

    /**
     * @ORM\Column(type="yandex_track_id")
     * @ORM\Id()
     */
    private $id;
    /**
     * @ORM\Column(type="integer")
     */
    private $trackId;
    /**
     * @ORM\Column(type="string")
     */
    private string $name;
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;
    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $finishedAt = null;
    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $status;
    /**
     * @ORM\Column(type="integer")
     */
    private int $bitrate;
    /**
     * @ORM\Column(type="integer")
     */
    private int $progress = 0;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $path = null;

    public function __construct(Id $id, int $trackId, \DateTimeImmutable $createdAt, string $name, int $bitrate)
    {
        $this->id = $id;
        $this->trackId = $trackId;
        $this->createdAt = $createdAt;
        $this->name = $name;
        $this->bitrate = $bitrate;
        $this->status = self::STATUS_NEW;
        $this->recordEvent(new TrackCreated($this->id, $this->trackId, $bitrate));
    }

    public function toProgress(): void
    {
        /*if($this->isProgress()) {
            throw new \DomainException("The entity is already in progress.");
        }*/
        $this->status = self::STATUS_PROGRESS;
    }

    public function finish(\DateTimeImmutable $date, string $path)
    {
        if($this->isSuccess()) {
            throw new \DomainException("The entity is already finished.");
        }
        $this->progress = 100;
        $this->finishedAt = $date;
        $this->path = $path;
        $this->status = self::STATUS_SUCCESS;
        $this->recordEvent(new TrackDownloaded($this->id, $this->trackId, $this->bitrate, $path));
    }

    public function setProgress(int $progress)
    {
        if(!$this->isProgress()) {
            throw new \DomainException('Progress percentage can be set only for entities in progress.');
        }
        $this->progress = $progress;
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isProgress(): bool
    {
        return $this->status === self::STATUS_PROGRESS;
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
     * @return \DateTimeImmutable
     */
    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTimeImmutable $finishedAt
     */
    public function setFinishedAt(?\DateTimeImmutable $finishedAt): self
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;
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
     * @return int
     */
    public function getProgress(): int
    {
        return $this->progress;
    }

    /**
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
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

<?php

declare(strict_types=1);

namespace App\Entity\Service\Yandex;

use App\Entity\Service\Yandex\Track\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="service_yandex_tracks")
 */
class Track
{
    const STATUS_NEW = 'new';
    const STATUS_SUCCESS = 'success';
    const STATUS_PROGRESS = 'progress';

    /**
     * @ORM\Column(type="yandex_track_id")
     * @ORM\Id()
     */
    private $id;
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
    private \DateTimeImmutable $finishedAt;
    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $status;
    /**
     * @ORM\Column(type="integer")
     */
    private int $bitrate;
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $path;

    public function __construct(Id $id, \DateTimeImmutable $createdAt, string $name, int $bitrate)
    {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->name = $name;
        $this->bitrate = $bitrate;
    }

    public function finish(\DateTimeImmutable $date, string $path)
    {
        if($this->isSuccess()) {
            throw new \DomainException("The entity is already finished.");
        }
        $this->date = $date;
        $this->path = $path;
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
    public function getFinishedAt(): \DateTimeImmutable
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTimeImmutable $finishedAt
     */
    public function setFinishedAt(\DateTimeImmutable $finishedAt): self
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
     * @return string
     */
    public function getPath(): string
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

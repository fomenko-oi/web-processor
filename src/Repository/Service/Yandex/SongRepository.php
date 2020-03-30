<?php

declare(strict_types=1);

namespace App\Repository\Service\Yandex;

use App\Entity\Service\Yandex\Track;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class SongRepository
{
    private $em;
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repo = $em->getRepository(Track::class);
    }

    public function get(Track\Id $id): Track
    {
        /** @var Track $track */
        if (!$track = $this->repo->find($id->getValue())) {
            throw new EntityNotFoundException('Track is not found.');
        }
        return $track;
    }

    public function getByTrackId(int $id): Track
    {
        /** @var Track $track */
        if (!$track = $this->repo->findOneBy(['trackId' => $id])) {
            throw new EntityNotFoundException('Track is not found.');
        }
        return $track;
    }

    public function add(Track $track): void
    {
        $this->em->persist($track);
    }
}

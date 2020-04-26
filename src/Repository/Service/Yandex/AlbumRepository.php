<?php declare(strict_types=1);

namespace App\Repository\Service\Yandex;

use App\Entity\Service\Yandex\Album;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

class AlbumRepository
{
    private $em;
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repo = $em->getRepository(Album::class);
    }

    public function get(Album\Id $id): Album
    {
        /** @var Album $album */
        if (!$album = $this->repo->find($id->getValue())) {
            throw new EntityNotFoundException('Album is not found.');
        }
        return $album;
    }

    public function getAlbumById(int $id): Album
    {
        /** @var Album $album */
        if (!$album = $this->repo->findOneBy(['albumId' => $id])) {
            throw new EntityNotFoundException('Album is not found.');
        }
        return $album;
    }

    public function add(Album $album): void
    {
        $this->em->persist($album);
    }
}

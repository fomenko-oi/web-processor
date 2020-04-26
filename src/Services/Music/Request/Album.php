<?php declare(strict_types=1);

namespace App\Services\Music\Request;

use App\Services\Music\Entity\Album\Album as AlbumResource;
use App\Services\Music\Yandex\Yandex;
use App\UseCases\Song\SongService;

class Album
{
    /**
     * @var SongService
     */
    private Yandex $parent;

    public function __construct(Yandex $parent)
    {
        $this->parent = $parent;
    }

    public function getDetails(int $id): AlbumResource
    {
        $data = $this->parent->parser->get("albums/{$id}/with-tracks");

        return AlbumResource::fromRequest($data['result']);
    }
}

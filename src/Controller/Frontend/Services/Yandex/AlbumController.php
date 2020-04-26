<?php

declare(strict_types=1);

namespace App\Controller\Frontend\Services\Yandex;

use App\Entity\Service\Yandex\Album;
use App\Entity\Service\Yandex\Track;
use App\Infrastructure\Flusher;
use App\Repository\Service\Yandex\AlbumRepository;
use App\Repository\Service\Yandex\SongRepository;
use App\Requests\Service\Yandex\Info as InfoCommand;
use App\Requests\Service\Yandex\Download as DownloadCommand;
use App\Resources\Service\Yandex\AlbumResource;
use App\Resources\Service\Yandex\AlbumSongsResource;
use App\Resources\Service\Yandex\LyricsResource;
use App\Resources\Service\Yandex\TrackResource;
use App\UseCases\Song\AlbumService;
use App\UseCases\Song\SongService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/yandex/album", name="yandex.album.", options={"expose"=true})
 */
class AlbumController extends AbstractController
{
    /**
     * @var SongService
     */
    private $songService;
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var SongRepository
     */
    private SongRepository $songs;
    /**
     * @var Flusher
     */
    private Flusher $flusher;
    /**
     * @var AlbumService
     */
    private AlbumService $albumService;
    /**
     * @var AlbumRepository
     */
    private AlbumRepository $albums;

    public function __construct(ValidatorInterface $validator, SongService $songService, AlbumService $albumService, SerializerInterface $serializer, SongRepository $songs, AlbumRepository $albums, Flusher $flusher)
    {
        $this->validator = $validator;
        $this->songService = $songService;
        $this->serializer = $serializer;
        $this->songs = $songs;
        $this->flusher = $flusher;
        $this->albumService = $albumService;
        $this->albums = $albums;
    }

    /**
     * @Route("/song", name="process", methods={"POST"})
     */
    public function handleAlbum(Request $request)
    {
        $storageUrl = $this->getParameter('storage_dir') . '/songs';

        $filePath = $this->songService->download($request->get('url'), $storageUrl);

        return $this->file($filePath);
    }

    /**
     * @Route("/info", name="info", methods={"POST"})
     */
    public function albumInfo(Request $request)
    {
        /** @var InfoCommand $command */
        $command = $this->serializer->deserialize($request->getContent(), InfoCommand::class, 'json', [
            'object_to_populate' => new InfoCommand()
        ]);

        $violations = $this->validator->validate($command);

        if($violations->count() > 0) {
            $json = $this->serializer->serialize($violations, 'json');
            return new JsonResponse($json, 400, [], true);
        }

        try {
            $data = new AlbumSongsResource($this->albumService->getAlbumInfo($command->id));

            return $this->json(['success' => true, 'data' => $data->toArray()]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/download/{fileId}", name="frontend.download")
     */
    public function download($fileId)
    {
        $song = $this->songs->get(new Track\Id($fileId));

        $path = $this->getParameter('storage_dir') . '/' . $song->getPath();

        if(!$song->isSuccess() || !file_exists($path)) {
            throw new \DomainException('File unavailable.');
        }

        return $this->file($path);
    }

    /**
     * @Route("/download", name="download")
     */
    public function albumDownload(Request $request)
    {
        /** @var DownloadCommand $command */
        $command = $this->serializer->deserialize($request->getContent(), DownloadCommand::class, 'json', [
            'object_to_populate' => new DownloadCommand()
        ]);

        $violations = $this->validator->validate($command);

        if($violations->count() > 0) {
            $json = $this->serializer->serialize($violations, 'json');
            return new JsonResponse($json, 400, [], true);
        }

        try {
            $albumInfo = $this->albumService->getAlbumInfo($command->id);

            $album = new Album(
                Album\Id::next(),
                $albumInfo->id,
                new \DateTimeImmutable(),
                $albumInfo->title,
                $command->bitrate
            );

            foreach ($albumInfo->volumes as $track) {
                $info = new Track(
                    Track\Id::next(),
                    (int)$track->id,
                    new \DateTimeImmutable(),
                    $track->title,
                    $command->bitrate
                );
                $album->addTrack($info);
                $this->songs->add($info);
            }

            $this->albums->add($album);
            $this->flusher->flush(...$album->getTracks());

            return $this->json(['success' => true, 'data' => $album]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/status", name="status")
     */
    public function albumStatus(Request $request)
    {
        $data = json_decode($request->getContent());

        try {
            $info = $this->albums->get(new Album\Id($data->id));

            return $this->json(['success' => true, 'data' => $info]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/{albumId?}", name="index")
     */
    public function album($albumId = null)
    {
        return $this->render('app/services/yandex/album.html.twig', [
            'albumId' => $albumId
        ]);
    }
}

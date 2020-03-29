<?php

namespace App\Controller\Frontend\Services;

use App\Requests\Service\Yandex\Info as InfoCommand;
use App\Requests\Service\Yandex\Download as DownloadCommand;
use App\Resources\Service\Yandex\TrackResource;
use App\UseCases\Song\SongService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/yandex", name="yandex.", options={"expose"=true})
 */
class YandexController extends AbstractController
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

    public function __construct(ValidatorInterface $validator, SongService $songService, SerializerInterface $serializer)
    {
        $this->validator = $validator;
        $this->songService = $songService;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('app/main/index.html.twig', []);
    }

    /**
     * @Route("/song", name="song")
     */
    public function song()
    {
        return $this->render('app/main/index.html.twig', []);
    }

    /**
     * @Route("/song-info", name="song.info")
     */
    public function songInfo(Request $request)
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
            // TODO cache this request
            $data = new TrackResource($this->songService->getTrackInfo($command->id));

            return $this->json(['success' => true, 'data' => $data->toArray()]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/song-download", name="song.download")
     */
    public function songDownload(Request $request)
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
            $storageUrl = sprintf(sprintf($this->getParameter('storage_dir') . '/songs/%d', date('md')));

            if(!file_exists($storageUrl)) {
                mkdir($storageUrl);
            }

            $filePath = $this->songService->download($command, $storageUrl);

            return $this->json(['success' => true, 'data' => [
                'url' => $filePath
            ]]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * @Route("/album", name="album")
     */
    public function album()
    {
        return $this->render('app/main/index.html.twig', []);
    }

    /**
     * @Route("/song", name="song.process", methods={"POST"})
     */
    public function handleSong(Request $request)
    {
        $storageUrl = $this->getParameter('storage_dir') . '/songs';

        $filePath = $this->songService->download($request->get('url'), $storageUrl);

        return $this->file($filePath);
    }
}

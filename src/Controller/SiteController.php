<?php

namespace App\Controller;

use App\Entity\Main\Contact\Contact;
use App\Entity\Main\Contact\Form;
use App\Entity\Service\Yandex\Track;
use App\Infrastructure\Flusher;
use App\Repository\Service\Yandex\SongRepository;
use App\Services\Common\Guzzle\Middleware\ProxyPoolMiddleware;
use App\Services\Music\Yandex\BaseClient;
use App\Services\Music\Yandex\Captcha\MlRecognizer\Recognize;
use App\Services\Music\Yandex\Yandex;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(options={"expose"=true})
 */
class SiteController extends AbstractController
{
    /**
     * @var \Swift_Mailer
     */
    private \Swift_Mailer $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/contacts", name="contacts")
     */
    public function contacts(Request $request)
    {
        $form = $this->createForm(Form::class, $contact = new Contact());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $message = (new \Swift_Message('New contact request'))
                    ->setTo($this->getParameter('admin_email'))
                    ->setBody($this->renderView('mail/contacts.html.twig', [
                        'message' => $contact->message,
                        'sender' => $contact->email,
                    ]), 'text/html');

                $this->mailer->send($message);
                $this->addFlash('success', 'Your request was send successful.');

                return $this->redirectToRoute('contacts');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/main/contacts.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/testprogress")
     */
    public function testprogress(CacheItemPoolInterface $cache)
    {
        set_time_limit(0);

        $iteration = 0;

        $client = new Client([
            RequestOptions::PROGRESS => function($dl_total_size, $dl_size_so_far, $ul_total_size, $ul_size_so_far) use(&$iteration) {
                $percent = $dl_total_size > 0 ? floor($dl_size_so_far / $dl_total_size * 100) : 0;

                if($percent === 0) {
                    return;
                }

                if($iteration % 10 === 0 || $percent === 100) {
                    echo 'save to DB ' . $percent . PHP_EOL;
                }

                $iteration++;
            },
            'save_to' => dirname(__DIR__) . '/../public/test.mp3'
        ]);

        $client->get('http://nginx/storage/songs/330/Counting%20Stars%20-%20OneRepublic.mp3');

        die('STOP');
    }

    /**
     * @Route("/testmiddleware")
     */
    public function testMiddleware(LoggerInterface $logger)
    {
        $client = new Client([
            RequestOptions::TIMEOUT => 3,
            RequestOptions::HEADERS => [
                'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.117 Safari/537.36'
            ]
        ]);

        $proxyList = [
            '46.191.226.105:3128', '51.158.172.165:8811', '51.91.212.159:3128', '51.158.123.35:8811', '51.158.165.18:8811', '51.158.186.242:8811',
            '62.33.207.201:80', '62.33.207.202:80', '62.33.207.201:3128', '178.238.126.91:8080', '5.189.133.231:80'
        ];
        $proxyList = [
            '51.91.212.159:3128', '51.158.123.35:8811', '51.158.165.18:8811'
        ];

        $client->getConfig('handler')->push(ProxyPoolMiddleware::create($proxyList, $logger));

        $res = $client->get('https://yandex.ru/');

        die($res->getBody());
    }

    /**
     * @Route("/testloginclient")
     */
    public function testloginclient()
    {
        /*array:4 [▼
  "token_type" => "bearer"
  "access_token" => "AgAAAAAUTnpDAAG8XoAqOFtVpkjwqWRB_HKacX0"
  "expires_in" => 31536000
  "uid" => 340687427
]*/

        $login = 'big-brother-228';
        $password = 'Fomenko97A';

        $proxy = '51.158.165.18:8811';
        //$proxy = '51.91.212.159:3128';

        $client = new BaseClient($login, $password, $this->getParameter('app_storage_dir'), $proxy);
        //$client->setToken();

        $yandex = new Yandex($client);
        $yandex->login();
        dd($yandex);

        $yandex->loginByCredentials($login, $password);

        dd($res);
    }

    /**
     * @Route("/testqueue")
     */
    public function testqueue(SongRepository $songs, Flusher $flusher)
    {
        //$song = $songs->get(new Track\Id('2b107c57-8691-4d72-a290-9cefd80798fb'));

        $track = new Track(
            Track\Id::next(),
            100500,
            new \DateTimeImmutable(),
            'Test name',
            320
        );

        $songs->add($track);
        $flusher->flush($track);

        dd($track);
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('app/main/index.html.twig');
    }
}

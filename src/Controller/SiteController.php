<?php

namespace App\Controller;

use App\Entity\Main\Contact\Contact;
use App\Entity\Main\Contact\Form;
use App\Entity\Service\Yandex\Track;
use App\Infrastructure\Flusher;
use App\Repository\Service\Yandex\SongRepository;
use App\Services\Common\Guzzle\Middleware\ProxyPoolMiddleware;
use App\Services\Music\Yandex\BaseClient;
use App\Services\Music\Yandex\Yandex;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Noxlogic\RateLimitBundle\Annotation\RateLimit;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route(options={"expose"=true})
 */
class SiteController extends AbstractController
{
    /**
     * @var \Swift_Mailer
     */
    private \Swift_Mailer $mailer;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(\Swift_Mailer $mailer, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
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
                $this->addFlash('success', $this->translator->trans('successful_request', [], 'app'));

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
     * @Route("/testtimer")
     */
    public function testTimer()
    {

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

        $yandex = new Yandex($client, $client);
        $yandex->login();
        dd($yandex);

        $yandex->loginByCredentials($login, $password);
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('app/main/index.html.twig');
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(options={"expose"=true})
 */
class SiteController extends AbstractController
{
    /**
     * @Route("/contacts", name="contacts")
     */
    public function contacts()
    {
        return new Response('contacts');
        //return $this->renderView('');
    }

    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        // TODO create index page for display all available services
        return $this->render('app/main/index.html.twig', []);
        //return $this->renderView('');
    }
}

<?php

namespace App\Controller;

/**
 * Main Controller for general pages
 */
class MainController extends Controller
{
    /**
     * The constructor will automatically inherit the setup from parent::Controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->showView('page/home.twig', [
            'current_page_title' => 'Inicio'
        ]);
    }

    public function privacy()
    {
        $this->showView('legal/privacy.twig', [
            'current_page_title' => 'Política de Privacidad'
        ]);
    }

    public function terms()
    {
        $this->showView('legal/terms.twig', [
            'current_page_title' => 'Términos y Condiciones'
        ]);
    }

    public function support()
    {
        $this->showView('legal/support.twig', [
            'current_page_title' => 'Soporte Técnico'
        ]);
    }

    public function contact()
    {
        $this->showView('page/contact.twig', [
            'current_page_title' => 'Contacto'
        ]);
    }
}

<?php

namespace SHRQ\SymposiumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function homepageAction()
    {
        return array();
    }

    /**
     * @Route("/static", name="static")
     * @Template()
     */
    public function staticAction()
    {
        return array();
    }

    /**
     * @Route("/dynamic", name="dynamic")
     * @Template()
     */
    public function dynamicAction()
    {
        return array();
    }
}

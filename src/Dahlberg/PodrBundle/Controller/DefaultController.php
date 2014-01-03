<?php
// src/Dahlberg/PodrBundle/Controller/DefaultController.php;
namespace Dahlberg\PodrBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {
    public function dashboardAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $mostLikedPodcasts = $em->getRepository('DahlbergPodrBundle:UserEpisode')
            ->findMostLikedPodcasts($user);

        return $this->render('DahlbergPodrBundle:Default:dashboard.html.twig', array(
            'mostLikedPodcasts' => $mostLikedPodcasts,
        ));
    }

    public function indexAction() {
        return $this->render('DahlbergPodrBundle:Default:index.html.twig', array());
    }
}

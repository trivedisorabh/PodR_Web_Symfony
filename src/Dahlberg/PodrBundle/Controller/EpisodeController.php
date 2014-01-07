<?php
// src/Dahlberg/PodrBundle/Controller/EpisodeController.php;
namespace Dahlberg\PodrBundle\Controller;

use Dahlberg\PodrBundle\Entity\UserEpisode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class EpisodeController extends Controller {
    public function archiveAction($id) {
        $em = $this->getDoctrine()->getManager();

        $userEpisode = $this->prepareUserEpisode($id);
        $userEpisode->setArchived(true);
        $userEpisode->setUnread(false);
        $userEpisode->setDateUpdated(new \DateTime('NOW'));

        $em->persist($userEpisode);
        $em->flush();

        return $this->redirect($this->generateUrl('episode_details', array('id' => $id)));
    }

    public function detailsAction($id) {
        $user = $this->get('security.context')->getToken()->getUser();
        $episode = $this->getDoctrine()
            ->getRepository('DahlbergPodrBundle:Episode')
            ->find($id);
        $userEpisode = $this->getDoctrine()
            ->getRepository('DahlbergPodrBundle:UserEpisode')
            ->findOneBy(array('episode' => $episode, 'user' => $user));

        if(!$episode)
            throw $this->createNotFoundException('The episode does not exist');

        return $this->render('DahlbergPodrBundle:Episode:details.html.twig', array(
            'episode' => $episode,
            'userEpisode' => $userEpisode,
        ));
    }

    public function dislikeAction($id) {
        $em = $this->getDoctrine()->getManager();

        $userEpisode = $this->prepareUserEpisode($id);
        $userEpisode->setRating(-1);
        $userEpisode->setUnread(false);
        $userEpisode->setDateUpdated(new \DateTime('NOW'));

        $em->persist($userEpisode);
        $em->flush();

        return $this->redirect($this->generateUrl('episode_details', array('id' => $id)));
    }
    public function indexAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $stashed = $em->getRepository('DahlbergPodrBundle:UserEpisode')
            ->findStashedEpisodes($user);
        $unarchived = $em->getRepository('DahlbergPodrBundle:UserEpisode')
            ->findUnarchivedEpisodes($user);
        $unread = $em->getRepository('DahlbergPodrBundle:UserEpisode')
            ->findUnreadEpisodes($user);

        return $this->render('DahlbergPodrBundle:Episode:index.html.twig', array(
            'stashed' => $stashed,
            'unarchived' => $unarchived,
            'unread' => $unread,
        ));
    }

    public function likeAction($id) {
        $em = $this->getDoctrine()->getManager();

        $userEpisode = $this->prepareUserEpisode($id);
        $userEpisode->setRating(1);
        $userEpisode->setUnread(false);
        $userEpisode->setDateUpdated(new \DateTime('NOW'));

        $em->persist($userEpisode);
        $em->flush();

        return $this->redirect($this->generateUrl('episode_details', array('id' => $id)));
    }

    public function restoreAction($id) {
        $em = $this->getDoctrine()->getManager();

        $userEpisode = $this->prepareUserEpisode($id);
        $userEpisode->setArchived(false);
        $userEpisode->setUnread(false);
        $userEpisode->setDateUpdated(new \DateTime('NOW'));

        $em->persist($userEpisode);
        $em->flush();

        return $this->redirect($this->generateUrl('episode_details', array('id' => $id)));
    }

    public function stashAction($id) {
        $em = $this->getDoctrine()->getManager();

        $userEpisode = $this->prepareUserEpisode($id);
        $userEpisode->setStashed(true);
        $userEpisode->setUnread(false);
        $userEpisode->setDateUpdated(new \DateTime('NOW'));

        $em->persist($userEpisode);
        $em->flush();

        return $this->redirect($this->generateUrl('episode_details', array('id' => $id)));
    }

    public function unstashAction($id) {
        $em = $this->getDoctrine()->getManager();

        $userEpisode = $this->prepareUserEpisode($id);
        $userEpisode->setStashed(false);
        $userEpisode->setUnread(false);
        $userEpisode->setDateUpdated(new \DateTime('NOW'));

        $em->persist($userEpisode);
        $em->flush();

        return $this->redirect($this->generateUrl('episode_details', array('id' => $id)));
    }

    public function updateTimeAction($id, $time) {
        $em = $this->getDoctrine()->getManager();

        $userEpisode = $this->prepareUserEpisode($id);
        $userEpisode->setCurrentTime($time);
        $userEpisode->setUnread(false);
        $userEpisode->setDateUpdated(new \DateTime('NOW'));

        $em->persist($userEpisode);
        $em->flush();

        return $this->redirect($this->generateUrl('episode_details', array('id' => $id)));
    }

    /* API */
    public function getEpisodeAction($id) {
        //$user = $this->get('security.context')->getToken()->getUser();
        $episode = $this->getDoctrine()
            ->getRepository('DahlbergPodrBundle:Episode')
            ->find($id);

        $response = new JsonResponse();
        $response->setData(array(
            'episode' => $episode,
        ));
        return $response;
    }

    /* NOT ACTIONS */

    public function prepareUserEpisode($episodeId) {
        $user = $this->get('security.context')->getToken()->getUser();
        $episode = $this->getDoctrine()
            ->getRepository('DahlbergPodrBundle:Episode')
            ->find($episodeId);

        if(!$episode)
            throw $this->createNotFoundException('The episode does not exist');

        $userEpisode = $this->getDoctrine()
            ->getRepository('DahlbergPodrBundle:UserEpisode')
            ->findOneBy(array('episode' => $episode, 'user' => $user));

        if(!$userEpisode) {
            $userEpisode = new UserEpisode();
            $userEpisode->setEpisode($episode);
            $userEpisode->setUser($user);
        }

        return $userEpisode;
    }
}
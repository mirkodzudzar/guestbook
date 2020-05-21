<?php

namespace App\Controller;

use Twig\Environment;
use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Environment $twig, ConferenceRepository $conferenceRepository)
    {
			return new Response($twig->render('conference/index.html.twig', [
				'conferences' => $conferenceRepository->findAll(),
			]));
		}

		/**
     * @Route("/conference/{id}", name="conference")
     */
		public function show(Environment $twig, Conference $conference, CommentRepository $commentRepository)
		{
			return new Response($twig->render('conference/show.html.twig', [
				'conference' => $conference,
				'comments' => $commentRepository->findBy(['conference' => $conference], ['createdAt' => 'DESC']),
			]));
		}
}

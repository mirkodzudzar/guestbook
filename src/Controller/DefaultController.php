<?php

namespace App\Controller;

use DateTime;
use App\Entity\Exp;
use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="user")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['id' => $id]);

        // $user->setFullname('Marko Markovic');

        // original Exps
        $originalExps = new ArrayCollection();
        foreach ($user->getExp() as $exp) {
            $originalExps->add($exp);
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // get rid of the onew that the user got rid of in the interface (DOM)
            foreach ($originalExps as $exp) {
                // check if the exp is in the $user->getExp()
                if ($user->getExp()->contains($exp) === false) {
                    $em->remove($exp);
                }
            }
            $em->persist($user);
            $em->flush();
        }

        return $this->render('default/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

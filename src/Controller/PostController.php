<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Services\FileUploader;
use App\Services\Notification;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
     * @Route("/post", name="post.")
     */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PostRepository $postRepository)
    {
        $posts = $postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("/create", name="create")
     * @param Request $request
     * @return Response
     */
    public function create(Request $request, FileUploader $fileUploader, Notification $notification)
    {
        // reate a new post with title.
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // entity manager
            $em = $this->getDoctrine()->getManager();

            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();
            if ($file) {
                $filename = $fileUploader->uploadFile($file);

                $post->setImage($filename);
            }

            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('post.index'));
        }

        $this->addFlash('success', 'Your post has been created.');

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @Route("/show/{id}", name="show")
     * @param Post $post
     * @return Response
     */
    public function show(Post $post)
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);

    }

    /**
     * @Route("/delete/{id}", name="delete")
     * @param Post $post
     * @return Response
     */
    public function remove(Post $post)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Your post has been removed.');

        return $this->redirect($this->generateUrl('post.index'));
    }

}

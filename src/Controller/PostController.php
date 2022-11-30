<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post/create')]
    public function create (Request $request, ManagerRegistry $doctrine): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $em = $doctrine->getManager();
            $em->persist($post);
            $em->flush();

            $this->addFlash('error', 'Post successfully created !');

            return new RedirectResponse("/post/list");
        }

        return $this->renderForm("post/form.html.twig", ['form' => $form, 'label' => 'Create']);
    }

    #[Route('/post/list')]
    public function list (PostRepository $postRepository, CommentRepository $commentRepository): Response
    {
        $posts = $postRepository->findAllWithCommentsCount($commentRepository);
        return $this->render("post/list.html.twig", ['posts' => $posts]);
    }

    #[Route('/post/delete/{post}', methods: ['POST'])]
    public function delete (Post $post, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        $em->remove($post);
        $em->flush();
        return new RedirectResponse('/post/list');
    }

    #[Route('/post/{post}')]
    public function show (Post $post, CommentRepository $commentRepository, Request $request, ManagerRegistry $doctrine): Response
    {
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setPost($post);
            $em = $doctrine->getManager();
            $em->persist($comment);
            $em->flush();
            return $this->redirectToRoute('app_post_show', ['post' => $post->getId()]);
        }

        $comments = $commentRepository->findBy(['post' => $post]);
        return $this->renderForm("post/show.html.twig", [
            'post' => $post,
            'comments' => $comments,
            'form' => $form
        ]);
    }

    #[Route('/post/edit/{post}')]
    public function edit (
        Post $post,
        Request $request,
        ManagerRegistry $doctrine
    ): Response
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $em = $doctrine->getManager();
            $em->flush();
            return new RedirectResponse("/post/list");
        }

        return $this->renderForm("post/form.html.twig", ['form' => $form, 'label' => 'Edit']);
    }
}
<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/post/create')]
    function create (Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $post->setUser($this->getUser());
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('app_post_list');
        }

        return $this->renderForm('post/create.html.twig', [
           'title' => 'Post creation',
           'form' => $form
        ]);
    }

    #[Route('/')]
    function list (PostRepository $postRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $posts = $postRepository->findAll();

        $response = $this->render('post/list.html.twig', [
            'title' => 'Post list',
            'posts' => $posts
        ]);

        $response->setPublic();
        $response->setMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate', true);

        return $response;
    }

    #[Route('/post/search')]
    function search (PostRepository $postRepository, Request $request): Response
    {
        $query = $request->query->get('query');
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $posts = $postRepository->searchByName($query);

        return $this->render('post/list.html.twig', [
            'title' => 'Post list',
            'posts' => $posts
        ]);
    }

    #[Route('/post/edit/{post}')]
    function edit (Post $post, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('edit', $post);
        $form = $this->createForm(PostType::class, $post);

        $form->remove('password');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $em->persist($post);
            $em->flush();
            return $this->redirectToRoute('app_post_list');
        }

        return $this->renderForm('post/create.html.twig', [
            'title' => 'Post edition',
            'form' => $form
        ]);
    }

    #[Route('/post/delete/{post}')]
    function delete (Post $post, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('edit', $post);
        $em->remove($post);
        $em->flush();
        return $this->redirectToRoute('app_post_list');
    }
}
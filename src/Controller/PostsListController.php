<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection; // Import the correct class
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Import the AbstractController class

class PostsListController extends AbstractController
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    #[Route('/posts-list', name: 'posts_list')]
    public function displayPosts(): Response
    {
        $sql = "SELECT * FROM posts ORDER BY date_of_creation DESC";
        $posts = $this->connection->fetchAllAssociative($sql);

        return $this->render('list.html.twig', [
            'pageTitle' => 'Posts List',
            'pageDescription' => 'Browse through the list of posts', 'posts' => $posts,
        ]);  //  
    }

    // private function displayPost($posts): Response {
    //     echo '<div class="post" data-post-id="' . $posts['id'] . '">';
    //     echo "<h3>{$posts['title']}</h3>";
    //     echo "<p>{$posts['content']}</p>";
    //     echo "<p>Created on: {$posts['date_of_creation']}</p>";

    //     if ($this->creatorId === $posts['creator_id']) {
    //         echo "<button class='delete-button' data-post-id='{$posts['id']}' data-creator-id='{$posts['creator_id']}'>Delete</button>";
    //     }

    //     echo "</div>";
    //     echo "<hr>";
    // }


    // private function getPosts(): array
    // {
    //     $query = "SELECT * FROM posts ORDER BY date_of_creation DESC";
    //     $posts = $this->connection->executeQuery($query);
    //     return $result->fetchAllAssociative();
    // }

    




    // public function getCreatorId(): string
    // {
    //     $request = $this->requestStack->getCurrentRequest();
    //     $creatorId = $request->get('creator_id');
    //     print($creatorId);
    //     if (!$creatorId) {
    //         $creatorId = uniqid();
    //         $response = new Response();
    //         $response->headers->setCookie(new Cookie('creator_id', $creatorId, time() + (86400 * 365), '/'));
    //         $response->send();
    //     }
    //     return $creatorId;
    // }
}
<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubmitPostController extends AbstractController
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    #[Route('/submit-post', name: 'submit_post', methods: ['GET', 'POST'])]
    public function submitPost(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $postTitle = htmlspecialchars($request->request->get('title'), ENT_QUOTES, 'UTF-8');
            $postContent = htmlspecialchars($request->request->get('content'), ENT_QUOTES, 'UTF-8');

            if (strlen($postContent) > 255) {
                echo"<div id='notification-message' class='notification-message'>
                <h3>Notification: The maximum post size is set to 255 characters.</h3>
                </div>";
            } elseif (empty($postTitle) || empty($postContent)) {
                echo "<div id='notification-message' class='notification-message'>
                <h3>Notification: Please enter both a post title and content.</h3>
                </div>";
            } else {
                $creatorId = $this->getCreatorId($request);

                if ($this->canMakePost($creatorId)) {
                    $query = "INSERT INTO posts (title, content, creator_id) VALUES (?, ?, ?)";
                    $rowCount = $this->connection->executeUpdate($query, [$postTitle, $postContent, $creatorId]);
                
                    $query2 = "INSERT INTO authors (creator_id, last_creation) VALUES (?, NOW()) 
                               ON CONFLICT (creator_id) DO UPDATE SET last_creation = EXCLUDED.last_creation";
                    $rowCount2 = $this->connection->executeUpdate($query2, [$creatorId]);
                
                    if ($rowCount > 0 && $rowCount2 !== false && $rowCount2 > 0) {
                        return $this->redirectToRoute('posts_list');
                    } else {
                        echo "Error: Failed to update the last creation.";
                    }
                } else {
                    echo "<div id='notification-message' class='notification-message'>
                                     <h3>Notification: You can only make one post every 10 minutes.</h3>
                                     </div>";
                }
                
            }
        }

        return $this->render('submit.html.twig', [
            'pageTitle' => 'Submit Post',
            'pageDescription' => 'The maximum post content is limited to 255 characters',
        ]);
        

    }

    private function getCreatorId(Request $request): string
    {
        $creatorId = $request->cookies->get('creator_id');
        if (!$creatorId) {
            $creatorId = uniqid();
            $request->cookies->set('creator_id', $creatorId);
        }
        return $creatorId;
    }

    private function canMakePost(string $creatorId): bool
    {
        $query = "SELECT last_creation FROM authors WHERE creator_id = ?";
        $lastCreation = $this->connection->fetchOne($query, [$creatorId]);

        if ($lastCreation === false) {
            return true; // New user, can make a post
        }

        $currentTime = new \DateTime();
        $lastCreationTime = new \DateTime($lastCreation);

        $interval = $lastCreationTime->diff($currentTime);
        $minutesPassed = $interval->i;

        return $minutesPassed >= 10; {
            return true; // Can make a post
        } 
    
    }
}

?>

<style>
    .notification-message {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #f5f5f5;
        border: 1px solid #ccc;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#notification-message').fadeIn();
        setTimeout(function() {
            $('#notification-message').fadeOut();
        }, 10000);
    });
</script>

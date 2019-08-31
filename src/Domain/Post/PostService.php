<?php

namespace App\Domain\Post;

use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Domain\DomainException\DomainRecordValidator;
use App\Domain\User\UserRepository;
use DomainException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class PostService {

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /** 
     * @var PostValidator 
     */
    private $postValidator;

    /** 
     * @var LoggerInterface
     */
    private $logger;

    /**
     * PostService Constructor
     *
     * @param PostRepository $postRepository
     * @param UserRepository $userRepository
     */
    public function __construct(PostRepository $postRepository, UserRepository $userRepository, PostValidator $postValidator, LoggerInterface $logger)
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->postValidator = $postValidator;
        $this->logger = $logger;
    }

    /**
     * Get all posts from database
     *
     * @return array
     */
    public function getAllPosts() : array
    {
        return $this->postRepository->findAll();
    }

    /**
     * Get post by ID field
     *
     * @param int $id
     * @return Post|null
     */
    public function getPostById($id) : ?Post
    {
        return $this->postRepository->findById($id);
    }

    /**
     * Persist a post to Database
     *
     * @param Post $post
     * @return Post
     */
    public function createPost(Post $post) : Post
    {
        $user = $this->userRepository->findByUsername($post->getUsername());

        if (!isset($user)) {
            $this->logger->error("User owner of post `{$post->getUsername()}` doesn't exists");

            throw new DomainRecordNotFoundException("User owner of post `{$post->getUsername()}` doesn't exists");
        }

        $this->postValidator->setPost($post);
        $check = $this->postValidator->validate();

        if (!$check) {
            $this->logger->error("PostValidator launched FALSE");

            $messages = $this->postValidator->getMessagesErrors();
 
            throw new DomainRecordValidator($messages);
        }

        $this->postRepository->save($post);

        $this->logger->info("Post ID `{$post->getId()}` persist to database with success");

        return $post;
    }

    /**
     * Edit title and content field of a post
     *
     * @param int $id
     * @param array $args
     * @return Post
     */
    public function changeTitleAndContent($id, $args) : Post
    {
        $post = $this->postRepository->findById($id);

        if (!isset($post)) {
            $message = "Post of ID `${id}` doesn't exists";

            $this->logger->error($message);

            throw new DomainRecordNotFoundException($message);
        }

        if (isset($args['title']))
            $post->setTitle($args['title']);

        if (isset($args['content']))
            $post->setContent($args['content']);

        $this->postValidator->setPost($post);
        
        $check = $this->postValidator->validate($post);

        if (!$check) {
            $this->logger->error("PostValidator launched FALSE");

            $messages = $this->postValidator->getMessagesErrors();
 
            throw new DomainRecordValidator($messages);
        }

        $post = $this->postRepository->save($post);

        $this->logger->info("Post ID `{$post->getId()}` persist to database with success");

        return $post;
    }

}

?>
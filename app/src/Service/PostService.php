<?php
/**
 * Post service.
 */

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;


/**
 * Class PostService.
 */
class PostService implements PostServiceInterface

{
    /**
     * Post repository.
     */
    private PostRepository $postRepository;

    /**
     * Paginator.
     */
    private PaginatorInterface $paginator;

    private $security;

    /**
     * Constructor.
     *
     * @param PostRepository     $postRepository Post repository
     * @param PaginatorInterface $paginator      Paginator
     */
    public function __construct(PostRepository $postRepository, PaginatorInterface $paginator, Security $security)
    {
        $this->postRepository = $postRepository;
        $this->paginator = $paginator;
        $this->security = $security;
    }

    /**
     * Get paginated list.
     *
     * @param int  $page   Page number
     * @param User $author Author
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author): PaginationInterface
    {
        if($this->security->isGranted('ROLE_ADMIN')){
            return $this->paginator->paginate(

                $this->postRepository->queryAll(),
                $page,
                PostRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }else{
            return $this->paginator->paginate(

                $this->postRepository->queryByAuthor($author),
                $page,
                PostRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }

    }

    /**
     * Save entity.
     *
     * @param Post $post Post entity
     */
    public function save(Post $post): void
    {
        $this->postRepository->save($post);
    }

    /**
     * Delete entity.
     *
     * @param Post $post Post entity
     */
    public function delete(Post $post): void
    {
        $this->postRepository->delete($post);
    }
}

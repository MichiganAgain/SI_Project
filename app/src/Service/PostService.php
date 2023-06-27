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
     * Category service.
     */
    private CategoryServiceInterface $categoryService;

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
     * @param CategoryServiceInterface $categoryService
     * @param PostRepository           $postRepository
     * @param PaginatorInterface       $paginator
     * @param Security                 $security
     */
    public function __construct(CategoryServiceInterface $categoryService, PostRepository $postRepository, PaginatorInterface $paginator, Security $security)
    {
        $this->categoryService = $categoryService;
        $this->postRepository = $postRepository;
        $this->paginator = $paginator;
        $this->security = $security;
    }

    /**
     * Get paginated list.
     *
     * @param int   $page    Page number
     * @param User  $author  Author
     * @param array $filters filters
     *
     * @return PaginationInterface<string, mixed> Paginated list
     */
    public function getPaginatedList(int $page, User $author, array $filters = []): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->paginator->paginate(
                $this->postRepository->queryAll($filters),
                $page,
                PostRepository::PAGINATOR_ITEMS_PER_PAGE
            );
        }

        return $this->paginator->paginate(
            $this->postRepository->queryByAuthor($author, $filters),
            $page,
            PostRepository::PAGINATOR_ITEMS_PER_PAGE
        );
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

    /**
     * Prepare filters for the tasks list.
     *
     * @param array<string, int> $filters Raw filters from request
     *
     * @return array<string, object> Result array of filters
     */
    private function prepareFilters(array $filters): array
    {
        $resultFilters = [];
        if (!empty($filters['category_id'])) {
            $category = $this->categoryService->findOneById($filters['category_id']);
            if (null !== $category) {
                $resultFilters['category'] = $category;
            }
        }

        return $resultFilters;
    }
}

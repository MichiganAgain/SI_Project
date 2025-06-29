<?php

/**
 * Category Service.
 */

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Category Service.
 */
class CategoryService implements CategoryServiceInterface
{
    private CategoryRepository $categoryRepository;
    private PostRepository $postRepository;
    private PaginatorInterface $paginator;
    private Security $security;

    /**
     * Constructor.
     *
     * @param CategoryRepository $categoryRepository CategoryRepository
     * @param PostRepository     $postRepository     PostRepository
     * @param PaginatorInterface $paginator          PaginatorInterface
     * @param Security           $security           Security
     */
    public function __construct(CategoryRepository $categoryRepository, PostRepository $postRepository, PaginatorInterface $paginator, Security $security)
    {
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->paginator = $paginator;
        $this->security = $security;
    }

    /**
     * Save Category.
     *
     * @param Category $category category
     *
     * @return void return void
     */
    public function save(Category $category): void
    {
        if (null === $category->getId()) {
            $category->setCreatedAt(new \DateTimeImmutable());
        }
        $category->setUpdatedAt(new \DateTimeImmutable());

        $this->categoryRepository->save($category);
    }

    /**
     * Delete Category.
     *
     * @param Category $category category
     *
     * @return bool return bool
     */
    public function delete(Category $category): bool
    {
        if ($this->canBeDeleted($category)) {
            $this->categoryRepository->delete($category);

            return true;
        }

        return false;
    }

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Category $category): bool
    {
        try {
            $result = $this->postRepository->countByCategory($category);

            return !($result > 0);
        } catch (NoResultException|NonUniqueResultException $e) {
            return false;
        }
    }

    /**
     * Get the list of paginated items.
     *
     * @param int $page Page
     *
     * @return PaginationInterface PaginationInterface
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->categoryRepository->queryAll(),
            $page,
            PostRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }

    /*
     * Find by id.
     *
     * @param int $id Category id
     *
     * @return Category|null Category entity
     *
     * @throws NonUniqueResultException NonUniqueResultException
     */
    //    public function findOneById(int $id): ?Category
    //    {
    //        return $this->categoryRepository->findOneById($id);
    //    }
}

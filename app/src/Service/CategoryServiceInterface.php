<?php
/**
 * Category Service Interface.
 */

namespace App\Service;

use App\Entity\Category;

/**
 *  Category Service Interface.
 */
interface CategoryServiceInterface
{
    /**
     * @param Category $category category
     *
     * @return void return void
     */
    public function save(Category $category): void;

    /**
     * Can Category be deleted?
     *
     * @param Category $category Category entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Category $category): bool;

    /**
     * @param Category $category category
     *
     * @return bool return bool
     */
    public function delete(Category $category): bool;

    /**
     * @param int $id id
     *
     * @return Category|null return null
     */
//    public function findOneById(int $id): ?Category;
}

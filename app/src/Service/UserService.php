<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Security;

/**
 *
 */
class UserService implements UserServiceInterface
{

    private UserRepository $userRepository;
    private PostRepository $postRepository;

    private $security;

    public function __construct(UserRepository $userRepository, PostRepository $postRepository, PaginatorInterface $paginator, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->paginator = $paginator;
        $this->security = $security;
    }

    public function save(User $user): void
    {
//        if (null == $user->getId()) {
//            $user->setCreatedAt(new \DateTimeImmutable());
//        }
//        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->userRepository->save($user);
    }

    public function edit(User $user): void
    {
//        if (null == $user->getId()) {
//            $user->setCreatedAt(new \DateTimeImmutable());
//        }
//        $user->setUpdatedAt(new \DateTimeImmutable());

        $this->userRepository->edit($user);
    }

    public function delete(User $user): bool
    {
        if($this->canBeDeleted($user)){
            $this->userRepository->remove($user);
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Can User be deleted?
     *
     * @param User $user User entity
     *
     * @return bool Result
     */
    public function canBeDeleted(User $user): bool
    {
        try {
            $result = $this->postRepository->countByUser($user);

            return !($result > 0);
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            PostRepository::PAGINATOR_ITEMS_PER_PAGE
        );
    }
}
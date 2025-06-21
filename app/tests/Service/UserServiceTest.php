<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class UserServiceTest extends TestCase
{
    public function testSaveCallsUserRepository()
    {
        $user = new User();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('save')
            ->with($user);

        $service = new UserService(
            $userRepository,
            $this->createMock(PostRepository::class),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $service->save($user);
    }

    public function testEditCallsUserRepository()
    {
        $user = new User();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('edit')
            ->with($user);

        $service = new UserService(
            $userRepository,
            $this->createMock(PostRepository::class),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $service->edit($user);
    }

    public function testDeleteUserWhenCanBeDeleted()
    {
        $user = new User();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('remove')
            ->with($user);

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByUser')->willReturn(0);

        $service = new UserService(
            $userRepository,
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertTrue($service->delete($user));
    }

    public function testDeleteUserWhenCannotBeDeleted()
    {
        $user = new User();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->never())->method('remove');

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByUser')->willReturn(3);

        $service = new UserService(
            $userRepository,
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertFalse($service->delete($user));
    }

    public function testCanBeDeletedWhenNoPosts()
    {
        $user = new User();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByUser')->willReturn(0);

        $service = new UserService(
            $this->createMock(UserRepository::class),
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertTrue($service->canBeDeleted($user));
    }

    public function testCanBeDeletedWhenPostsExist()
    {
        $user = new User();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByUser')->willReturn(5);

        $service = new UserService(
            $this->createMock(UserRepository::class),
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertFalse($service->canBeDeleted($user));
    }

    public function testCanBeDeletedWhenExceptionThrown()
    {
        $user = new User();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->method('countByUser')->willThrowException(new NonUniqueResultException());

        $service = new UserService(
            $this->createMock(UserRepository::class),
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $this->assertFalse($service->canBeDeleted($user));
    }

    public function testGetPaginatedListReturnsPagination()
    {
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('queryAll')
            ->willReturn($queryBuilder);

        $pagination = $this->createMock(PaginationInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects($this->once())
            ->method('paginate')
            ->with($queryBuilder, 1, PostRepository::PAGINATOR_ITEMS_PER_PAGE)
            ->willReturn($pagination);

        $service = new UserService(
            $userRepository,
            $this->createMock(PostRepository::class),
            $paginator,
            $this->createMock(Security::class)
        );

        $this->assertSame($pagination, $service->getPaginatedList(1));
    }
}

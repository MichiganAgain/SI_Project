<?php
/**
 * UserService test cases.
 *
 * @license MIT
 */

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

/**
 * Class UserServiceTest.
 *
 * Unit tests for the UserService class.
 */
class UserServiceTest extends TestCase
{
    /**
     * Test that save() calls the UserRepository::save() method.
     */
    public function testSaveCallsUserRepository(): void
    {
        $user = new User();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())->method('save')->with($user);

        $service = new UserService(
            $userRepository,
            $this->createMock(PostRepository::class),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $service->save($user);
    }

    /**
     * Test that edit() calls the UserRepository::edit() method.
     */
    public function testEditCallsUserRepository(): void
    {
        $user = new User();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())->method('edit')->with($user);

        $service = new UserService(
            $userRepository,
            $this->createMock(PostRepository::class),
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $service->edit($user);
    }

    /**
     * Test that delete() removes a user when they have no posts.
     */
    public function testDeleteUserWhenCanBeDeleted(): void
    {
        $user = new User();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())->method('remove')->with($user);

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

    /**
     * Test that delete() does not remove a user when they have posts.
     */
    public function testDeleteUserWhenCannotBeDeleted(): void
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

    /**
     * Test that canBeDeleted() returns true when no posts exist.
     */
    public function testCanBeDeletedWhenNoPosts(): void
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

    /**
     * Test that canBeDeleted() returns false when posts exist.
     */
    public function testCanBeDeletedWhenPostsExist(): void
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

    /**
     * Test that canBeDeleted() returns false when an exception is thrown.
     */
    public function testCanBeDeletedWhenExceptionThrown(): void
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

    /**
     * Test that getPaginatedList() returns a PaginationInterface instance.
     */
    public function testGetPaginatedListReturnsPagination(): void
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

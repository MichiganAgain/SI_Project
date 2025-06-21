<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Service\CategoryServiceInterface;
use App\Service\PostService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PostServiceTest extends TestCase
{
    public function testSavePersistsPost()
    {
        $post = new Post();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects($this->once())->method('save')->with($post);

        $service = new PostService(
            $this->createMock(CategoryServiceInterface::class),
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $service->save($post);
    }

    public function testDeleteRemovesPost()
    {
        $post = new Post();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects($this->once())->method('delete')->with($post);

        $service = new PostService(
            $this->createMock(CategoryServiceInterface::class),
            $postRepository,
            $this->createMock(PaginatorInterface::class),
            $this->createMock(Security::class)
        );

        $service->delete($post);
    }

    public function testGetPaginatedListForAdmin()
    {
        $user = new User();
        $filters = [];

        $queryBuilder = $this->getMockBuilder(\Doctrine\ORM\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects($this->once())
            ->method('queryAll')
            ->with([])
            ->willReturn($queryBuilder);

        $pagination = $this->createMock(PaginationInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects($this->once())
            ->method('paginate')
            ->with($queryBuilder, 1, PostRepository::PAGINATOR_ITEMS_PER_PAGE)
            ->willReturn($pagination);

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);

        $service = new PostService(
            $this->createMock(CategoryServiceInterface::class),
            $postRepository,
            $paginator,
            $security
        );

        $result = $service->getPaginatedList(1, $user, $filters);
        $this->assertSame($pagination, $result);
    }

    public function testGetPaginatedListForRegularUser()
    {
        $user = new User();
        $filters = [];

        $queryBuilder = $this->getMockBuilder(\Doctrine\ORM\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $postRepository = $this->createMock(PostRepository::class);
        $postRepository->expects($this->once())
            ->method('queryByAuthor')
            ->with($user, [])
            ->willReturn($queryBuilder);

        $pagination = $this->createMock(PaginationInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator->expects($this->once())
            ->method('paginate')
            ->with($queryBuilder, 1, PostRepository::PAGINATOR_ITEMS_PER_PAGE)
            ->willReturn($pagination);

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        $service = new PostService(
            $this->createMock(CategoryServiceInterface::class),
            $postRepository,
            $paginator,
            $security
        );

        $result = $service->getPaginatedList(1, $user, $filters);
        $this->assertSame($pagination, $result);
    }

}

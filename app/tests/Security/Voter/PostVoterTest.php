<?php

namespace App\Tests\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use App\Security\Voter\PostVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class PostVoterTest extends TestCase
{
    private PostVoter $voter;
    private $securityMock;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->voter = new PostVoter($this->securityMock);
    }

    public function testAuthorCanEditViewAndDelete()
    {
        $user = new User();
        $post = $this->createMock(Post::class);
        $post->method('getAuthor')->willReturn($user);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        // Author should be able to edit, view, delete
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::EDIT]) === PostVoter::ACCESS_GRANTED);
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::VIEW]) === PostVoter::ACCESS_GRANTED);
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::DELETE]) === PostVoter::ACCESS_GRANTED);
    }

    public function testNonAuthorCannotEditViewOrDelete()
    {
        $user = new User();
        $otherUser = new User();

        $post = $this->createMock(Post::class);
        $post->method('getAuthor')->willReturn($otherUser);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        // Non-author should be denied access
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::EDIT]) === PostVoter::ACCESS_DENIED);
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::VIEW]) === PostVoter::ACCESS_DENIED);
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::DELETE]) === PostVoter::ACCESS_DENIED);
    }

    public function testAdminCanEditViewAndDelete()
    {
        $user = new User();
        $post = $this->createMock(Post::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);

        // Admin can always access
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::EDIT]) === PostVoter::ACCESS_GRANTED);
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::VIEW]) === PostVoter::ACCESS_GRANTED);
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::DELETE]) === PostVoter::ACCESS_GRANTED);
    }

    public function testVoteReturnsDeniedForUnsupportedAttributes()
    {
        $user = new User();
        $post = $this->createMock(Post::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->willReturn(false);

        $result = $this->voter->vote($token, $post, ['UNSUPPORTED']);

        // ACCESS_ABSTAIN oznacza, że voter nie obsługuje takiego atrybutu
        $this->assertEquals(PostVoter::ACCESS_ABSTAIN, $result);
    }


    public function testVoteReturnsDeniedForAnonymousUser()
    {
        $post = $this->createMock(Post::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null); // anonymous user

        $this->securityMock->method('isGranted')->willReturn(false);

        // Anonymous user should be denied
        $this->assertTrue($this->voter->vote($token, $post, [PostVoter::VIEW]) === PostVoter::ACCESS_DENIED);
    }
}

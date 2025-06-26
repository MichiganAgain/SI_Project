<?php
/**
 * PostVoter test cases.
 *
 * @license MIT
 */

namespace App\Tests\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use App\Security\Voter\PostVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class PostVoterTest.
 *
 * Test cases for PostVoter authorization logic.
 */
class PostVoterTest extends TestCase
{
    private PostVoter $voter;
    private Security $securityMock;

    /**
     * Test that author of a post can edit, view, and delete it.
     */
    public function testAuthorCanEditViewAndDelete(): void
    {
        $user = new User();
        $post = $this->createMock(Post::class);
        $post->method('getAuthor')->willReturn($user);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        $this->assertSame(PostVoter::ACCESS_GRANTED, $this->voter->vote($token, $post, [PostVoter::EDIT]));
        $this->assertSame(PostVoter::ACCESS_GRANTED, $this->voter->vote($token, $post, [PostVoter::VIEW]));
        $this->assertSame(PostVoter::ACCESS_GRANTED, $this->voter->vote($token, $post, [PostVoter::DELETE]));
    }

    /**
     * Test that non-author cannot edit, view, or delete the post.
     */
    public function testNonAuthorCannotEditViewOrDelete(): void
    {
        $user = new User();
        $otherUser = new User();

        $post = $this->createMock(Post::class);
        $post->method('getAuthor')->willReturn($otherUser);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);

        $this->assertSame(PostVoter::ACCESS_DENIED, $this->voter->vote($token, $post, [PostVoter::EDIT]));
        $this->assertSame(PostVoter::ACCESS_DENIED, $this->voter->vote($token, $post, [PostVoter::VIEW]));
        $this->assertSame(PostVoter::ACCESS_DENIED, $this->voter->vote($token, $post, [PostVoter::DELETE]));
    }

    /**
     * Test that admin can always access the post.
     */
    public function testAdminCanEditViewAndDelete(): void
    {
        $user = new User();
        $post = $this->createMock(Post::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);

        $this->assertSame(PostVoter::ACCESS_GRANTED, $this->voter->vote($token, $post, [PostVoter::EDIT]));
        $this->assertSame(PostVoter::ACCESS_GRANTED, $this->voter->vote($token, $post, [PostVoter::VIEW]));
        $this->assertSame(PostVoter::ACCESS_GRANTED, $this->voter->vote($token, $post, [PostVoter::DELETE]));
    }

    /**
     * Test that unsupported attribute returns ACCESS_ABSTAIN.
     */
    public function testVoteReturnsDeniedForUnsupportedAttributes(): void
    {
        $user = new User();
        $post = $this->createMock(Post::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->willReturn(false);

        $result = $this->voter->vote($token, $post, ['UNSUPPORTED']);
        $this->assertSame(PostVoter::ACCESS_ABSTAIN, $result);
    }

    /**
     * Test that anonymous user is denied access.
     */
    public function testVoteReturnsDeniedForAnonymousUser(): void
    {
        $post = $this->createMock(Post::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $this->securityMock->method('isGranted')->willReturn(false);

        $this->assertSame(PostVoter::ACCESS_DENIED, $this->voter->vote($token, $post, [PostVoter::VIEW]));
    }

    /**
     * Test that voter abstains from voting for unsupported subject type.
     */
    public function testVoteReturnsAbstainForUnsupportedSubject(): void
    {
        $user = new User();
        $unsupportedSubject = new \stdClass();

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->willReturn(false);

        $result = $this->voter->vote($token, $unsupportedSubject, [PostVoter::VIEW]);
        $this->assertSame(PostVoter::ACCESS_ABSTAIN, $result);
    }

    /**
     * Test that voteOnAttribute() returns false for an unknown attribute.
     */
    public function testVoteOnAttributeReturnsFalseForUnknownAttribute(): void
    {
        $user = new User();
        $post = $this->createMock(Post::class);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('isGranted')->willReturn(false);

        $method = new \ReflectionMethod(PostVoter::class, 'voteOnAttribute');
        $method->setAccessible(true);

        $result = $method->invoke($this->voter, 'UNKNOWN', $post, $token);
        $this->assertFalse($result);
    }

    /**
     * Sets up test dependencies.
     */
    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->voter = new PostVoter($this->securityMock);
    }
}

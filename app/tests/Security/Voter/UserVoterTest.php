<?php
/**
 * UserVoter test cases.
 *
 * @license MIT
 */

namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserVoterTest.
 *
 * Test cases for the UserVoter authorization logic.
 */
class UserVoterTest extends TestCase
{
    private UserVoter $voter;
    private Security $securityMock;

    /**
     * Test that admin user is granted access to all actions.
     */
    public function testAdminCanDoEverything(): void
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);

        $token->method('getUser')->willReturn($user);
        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);
        $this->securityMock->method('getUser')->willReturn($user);

        foreach ([UserVoter::EDIT, UserVoter::VIEW, UserVoter::DELETE, UserVoter::MANAGE] as $attribute) {
            $this->assertEquals(UserVoter::ACCESS_GRANTED, $this->voter->vote($token, $user, [$attribute]));
        }
    }

    /**
     * Test that a user can edit, view, and delete their own account.
     */
    public function testUserCanEditViewDeleteSelf(): void
    {
        $currentUser = new User();
        $currentUser->setUsername('john');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($currentUser);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->securityMock->method('getUser')->willReturn($currentUser);

        foreach ([UserVoter::EDIT, UserVoter::VIEW, UserVoter::DELETE] as $attribute) {
            $this->assertEquals(UserVoter::ACCESS_GRANTED, $this->voter->vote($token, $currentUser, [$attribute]));
        }
    }

    /**
     * Test that a user cannot edit, view, or delete another user's account.
     */
    public function testUserCannotEditViewDeleteOthers(): void
    {
        $currentUser = new User();
        $currentUser->setUsername('john');

        $otherUser = new User();
        $otherUser->setUsername('mary');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($currentUser);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->securityMock->method('getUser')->willReturn($currentUser);

        foreach ([UserVoter::EDIT, UserVoter::VIEW, UserVoter::DELETE] as $attribute) {
            $this->assertEquals(UserVoter::ACCESS_DENIED, $this->voter->vote($token, $otherUser, [$attribute]));
        }
    }

    /**
     * Test that only admin can manage users.
     */
    public function testManageRequiresAdmin(): void
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('getUser')->willReturn($user);

        $this->securityMock->expects($this->exactly(2))
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertEquals(UserVoter::ACCESS_DENIED, $this->voter->vote($token, null, [UserVoter::MANAGE]));
        $this->assertEquals(UserVoter::ACCESS_GRANTED, $this->voter->vote($token, null, [UserVoter::MANAGE]));
    }

    /**
     * Test that unsupported attributes result in abstain.
     */
    public function testVoteAbstainsForUnsupportedAttributes(): void
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);

        $token->method('getUser')->willReturn($user);
        $this->securityMock->method('getUser')->willReturn($user);
        $this->securityMock->method('isGranted')->willReturn(false);

        $this->assertEquals(UserVoter::ACCESS_ABSTAIN, $this->voter->vote($token, $user, ['UNSUPPORTED']));
    }

    /**
     * Test that anonymous users are denied access.
     */
    public function testAnonymousUserIsDenied(): void
    {
        $targetUser = new User();
        $token = $this->createMock(TokenInterface::class);

        $token->method('getUser')->willReturn(null);
        $this->securityMock->method('isGranted')->willReturn(false);
        $this->securityMock->method('getUser')->willReturn(null);

        $this->assertEquals(UserVoter::ACCESS_DENIED, $this->voter->vote($token, $targetUser, [UserVoter::VIEW]));
    }

    /**
     * Test that voteOnAttribute returns false for unknown attribute.
     */
    public function testVoteOnAttributeReturnsFalseForUnknownAttribute(): void
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);

        $token->method('getUser')->willReturn($user);
        $this->securityMock->method('getUser')->willReturn($user);
        $this->securityMock->method('isGranted')->willReturn(false);

        $method = new \ReflectionMethod(UserVoter::class, 'voteOnAttribute');
        $method->setAccessible(true);

        $result = $method->invoke($this->voter, 'UNKNOWN', $user, $token);
        $this->assertFalse($result);
    }

    /**
     * Sets up the test environment.
     */
    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->voter = new UserVoter($this->securityMock);
    }
}

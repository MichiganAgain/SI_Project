<?php

namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class UserVoterTest extends TestCase
{
    private UserVoter $voter;
    private $securityMock;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->voter = new UserVoter($this->securityMock);
    }

    public function testAdminCanDoEverything()
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

    public function testUserCanEditViewDeleteSelf()
    {
        $currentUser = new User();
        $currentUser->setUsername('john');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($currentUser);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->securityMock->method('getUser')->willReturn($currentUser);

        // User tries to access himself
        foreach ([UserVoter::EDIT, UserVoter::VIEW, UserVoter::DELETE] as $attribute) {
            $this->assertEquals(UserVoter::ACCESS_GRANTED, $this->voter->vote($token, $currentUser, [$attribute]));
        }
    }

    public function testUserCannotEditViewDeleteOthers()
    {
        $currentUser = new User();
        $currentUser->setUsername('john');

        $otherUser = new User();
        $otherUser->setUsername('mary');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($currentUser);

        $this->securityMock->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->securityMock->method('getUser')->willReturn($currentUser);

        // User tries to access another user
        foreach ([UserVoter::EDIT, UserVoter::VIEW, UserVoter::DELETE] as $attribute) {
            $this->assertEquals(UserVoter::ACCESS_DENIED, $this->voter->vote($token, $otherUser, [$attribute]));
        }
    }

    public function testManageRequiresAdmin()
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);

        $token->method('getUser')->willReturn($user);

        $this->securityMock->method('getUser')->willReturn($user);

        $this->securityMock->expects($this->exactly(2))
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturnOnConsecutiveCalls(false, true);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);
        $this->securityMock->method('getUser')->willReturn($user);

        $this->assertEquals(UserVoter::ACCESS_DENIED, $this->voter->vote($token, null, [UserVoter::MANAGE]));
        $this->assertEquals(UserVoter::ACCESS_GRANTED, $this->voter->vote($token, null, [UserVoter::MANAGE]));

    }

    public function testVoteAbstainsForUnsupportedAttributes()
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);

        $token->method('getUser')->willReturn($user);
        $this->securityMock->method('getUser')->willReturn($user);
        $this->securityMock->method('isGranted')->willReturn(false);

        $this->assertEquals(UserVoter::ACCESS_ABSTAIN, $this->voter->vote($token, $user, ['UNSUPPORTED']));
    }

    public function testAnonymousUserIsDenied()
    {
        $post = new User();
        $token = $this->createMock(TokenInterface::class);

        $token->method('getUser')->willReturn(null);
        $this->securityMock->method('isGranted')->willReturn(false);
        $this->securityMock->method('getUser')->willReturn(null);

        $this->assertEquals(UserVoter::ACCESS_DENIED, $this->voter->vote($token, $post, [UserVoter::VIEW]));
    }
}

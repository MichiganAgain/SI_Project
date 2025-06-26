<?php
/**
 * CategoryVoter test cases.
 *
 * @license MIT
 */

namespace App\Tests\Security\Voter;

use App\Entity\User;
use App\Security\Voter\CategoryVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class CategoryVoterTest.
 *
 * Test cases for CategoryVoter authorization logic.
 */
class CategoryVoterTest extends TestCase
{
    private Security $security;
    private CategoryVoter $voter;
    private TokenInterface $token;
    private User $user;

    /**
     * Test that admin user is granted access for MANAGE.
     */
    public function testAdminCanManage(): void
    {
        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);
        $this->token->method('getUser')->willReturn($this->user);

        $result = $this->voter->vote($this->token, null, [CategoryVoter::MANAGE]);

        $this->assertSame(CategoryVoter::ACCESS_GRANTED, $result);
    }

    /**
     * Test that anonymous user is denied access.
     */
    public function testAnonymousUserDenied(): void
    {
        $this->token->method('getUser')->willReturn('anon.');

        $result = $this->voter->vote($this->token, null, [CategoryVoter::MANAGE]);

        $this->assertSame(CategoryVoter::ACCESS_DENIED, $result);
    }

    /**
     * Test that unsupported attribute results in ACCESS_ABSTAIN.
     */
    public function testUnsupportedAttributeReturnsAbstain(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $result = $this->voter->vote($this->token, null, ['UNSUPPORTED']);

        $this->assertSame(CategoryVoter::ACCESS_ABSTAIN, $result);
    }

    /**
     * Test that unsupported subject results in ACCESS_ABSTAIN.
     */
    public function testUnsupportedSubjectReturnsAbstain(): void
    {
        $this->token->method('getUser')->willReturn($this->user);

        $result = $this->voter->vote($this->token, new \stdClass(), [CategoryVoter::VIEW]);

        $this->assertSame(CategoryVoter::ACCESS_ABSTAIN, $result);
    }

    /**
     * Sets up test dependencies.
     */
    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->voter = new CategoryVoter($this->security);
        $this->token = $this->createMock(TokenInterface::class);
        $this->user = new User();
    }
}

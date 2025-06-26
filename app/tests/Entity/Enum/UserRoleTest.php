<?php
/**
 * UserRoleTest test cases.
 *
 * @license MIT
 */

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\UserRole;
use PHPUnit\Framework\TestCase;

/**
 * Class UserRoleTest.
 *
 * Tests for the UserRole enum.
 */
class UserRoleTest extends TestCase
{
    /**
     * Test that enum cases return correct values.
     */
    public function testEnumCases(): void
    {
        $this->assertSame('ROLE_USER', UserRole::ROLE_USER->value);
        $this->assertSame('ROLE_ADMIN', UserRole::ROLE_ADMIN->value);
    }

    /**
     * Test that labels are returned correctly for each role.
     */
    public function testLabels(): void
    {
        $this->assertSame('label.role_user', UserRole::ROLE_USER->label());
        $this->assertSame('label.role_admin', UserRole::ROLE_ADMIN->label());
    }

    /**
     * Test that the enum has the expected number of cases.
     */
    public function testEnumCount(): void
    {
        $cases = UserRole::cases();
        $this->assertCount(2, $cases);
    }
}

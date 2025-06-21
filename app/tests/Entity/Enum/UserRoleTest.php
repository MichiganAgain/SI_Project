<?php

namespace App\Tests\Entity\Enum;

use App\Entity\Enum\UserRole;
use PHPUnit\Framework\TestCase;

/**
 * Class UserRoleTest.
 */
class UserRoleTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertSame('ROLE_USER', UserRole::ROLE_USER->value);
        $this->assertSame('ROLE_ADMIN', UserRole::ROLE_ADMIN->value);
    }

    public function testLabels(): void
    {
        $this->assertSame('label.role_user', UserRole::ROLE_USER->label());
        $this->assertSame('label.role_admin', UserRole::ROLE_ADMIN->label());
    }

    public function testEnumCount(): void
    {
        $cases = UserRole::cases();
        $this->assertCount(2, $cases);
    }
}

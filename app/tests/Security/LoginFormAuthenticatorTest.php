<?php
/**
 * LoginFormAuthenticator test cases.
 *
 * @license MIT
 */

namespace App\Tests\Security;

use App\Security\LoginFormAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class LoginFormAuthenticatorTest.
 *
 * Test cases for LoginFormAuthenticator logic.
 */
class LoginFormAuthenticatorTest extends TestCase
{
    private UrlGeneratorInterface $urlGenerator;
    private LoginFormAuthenticator $authenticator;

    /**
     * Test supports() returns true for login route with POST method.
     */
    public function testSupportsReturnsTrueForLoginRouteAndPost(): void
    {
        $request = new Request([], [], ['_route' => 'app_login']);
        $request->setMethod('POST');

        $this->assertTrue($this->authenticator->supports($request));
    }

    /**
     * Test supports() returns false for routes other than login.
     */
    public function testSupportsReturnsFalseForOtherRoutes(): void
    {
        $request = new Request([], [], ['_route' => 'some_other_route']);
        $request->setMethod('POST');

        $this->assertFalse($this->authenticator->supports($request));
    }

    /**
     * Test onAuthenticationSuccess returns RedirectResponse to target path from session.
     */
    public function testOnAuthenticationSuccessWithTargetPath(): void
    {
        $session = new \Symfony\Component\HttpFoundation\Session\Session(new MockArraySessionStorage());
        $session->set('_security.main.target_path', '/target-url');

        $request = new Request();
        $request->setSession($session);

        $token = $this->createMock(TokenInterface::class);

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/target-url', $response->getTargetUrl());
    }

    /**
     * Test onAuthenticationSuccess redirects to default route if no target path.
     */
    public function testOnAuthenticationSuccessWithNoTargetPath(): void
    {
        $request = new Request();
        $session = new \Symfony\Component\HttpFoundation\Session\Session(new MockArraySessionStorage());
        $request->setSession($session);

        $token = $this->createMock(TokenInterface::class);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(LoginFormAuthenticator::DEFAULT_ROUTE)
            ->willReturn('/post');

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/post', $response->getTargetUrl());
    }

    /**
     * Test getLoginUrl() using reflection.
     */
    public function testGetLoginUrlUsingReflection(): void
    {
        $request = new Request();

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(LoginFormAuthenticator::LOGIN_ROUTE)
            ->willReturn('/login');

        $reflection = new \ReflectionClass(LoginFormAuthenticator::class);
        $method = $reflection->getMethod('getLoginUrl');
        $method->setAccessible(true);

        $loginUrl = $method->invoke($this->authenticator, $request);

        $this->assertEquals('/login', $loginUrl);
    }

    /**
     * Sets up the test instance.
     */
    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->authenticator = new LoginFormAuthenticator($this->urlGenerator);
    }
}

<?php

namespace App\Tests\Security;

use App\Security\LoginFormAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginFormAuthenticatorTest extends TestCase
{
    private UrlGeneratorInterface $urlGenerator;
    private LoginFormAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->authenticator = new LoginFormAuthenticator($this->urlGenerator);
    }

    public function testSupportsReturnsTrueForLoginRouteAndPost(): void
    {
        $request = new Request([], [], ['_route' => 'app_login']);
        $request->setMethod('POST');

        $this->assertTrue($this->authenticator->supports($request));
    }

    public function testSupportsReturnsFalseForOtherRoutes(): void
    {
        $request = new Request([], [], ['_route' => 'some_other_route']);
        $request->setMethod('POST');

        $this->assertFalse($this->authenticator->supports($request));
    }

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

}

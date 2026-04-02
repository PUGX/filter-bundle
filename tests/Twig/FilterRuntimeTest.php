<?php

namespace PUGX\FilterBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use PUGX\FilterBundle\Filter;
use PUGX\FilterBundle\Twig\FilterRuntime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class FilterRuntimeTest extends TestCase
{
    public function testHasFalse(): void
    {
        /** @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('has')->willReturn(false);
        $request = Request::create('/');
        $request->setSession($session);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        // @phpstan-ignore-next-line method.unresolvableType
        $filter = $this->createStub(Filter::class);
        $filterRuntime = new FilterRuntime($requestStack, $filter);
        self::assertFalse($filterRuntime->has('foo'));
    }

    public function testHasTrue(): void
    {
        /** @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('has')->willReturn(true);
        $session->expects($this->once())->method('get')->willReturn('bar');
        $request = Request::create('/');
        $request->setSession($session);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        // @phpstan-ignore-next-line varTag.unresolvableType
        $filter = $this->createStub(Filter::class);
        $filterRuntime = new FilterRuntime($requestStack, $filter);
        self::assertTrue($filterRuntime->has('foo'));
    }
}

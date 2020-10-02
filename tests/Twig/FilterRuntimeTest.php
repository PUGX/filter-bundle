<?php

namespace PUGX\FilterBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use PUGX\FilterBundle\Twig\FilterRuntime;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class FilterRuntimeTest extends TestCase
{
    public function testHasFalse(): void
    {
        /** @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('has')->willReturn(false);
        $filter = new FilterRuntime($session);
        self::assertFalse($filter->has('foo'));
    }

    public function testHasTrue(): void
    {
        /** @var SessionInterface&\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $session->expects(self::once())->method('has')->willReturn(true);
        $session->expects(self::once())->method('get')->willReturn('bar');
        $filter = new FilterRuntime($session);
        self::assertTrue($filter->has('foo'));
    }
}

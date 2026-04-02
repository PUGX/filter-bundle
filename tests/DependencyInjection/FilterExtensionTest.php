<?php

namespace PUGX\FilterBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use PUGX\FilterBundle\DependencyInjection\FilterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FilterExtensionTest extends TestCase
{
    public function testLoadSetParameters(): void
    {
        $container = $this->createStub(ContainerBuilder::class);
        $extension = new FilterExtension();
        $extension->load([], $container);
        self::assertTrue(true); // @phpstan-ignore-line staticMethod.alreadyNarrowedType
    }

    public function testPrependWithoutTwig(): void
    {
        /** @var ContainerBuilder&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->once())->method('hasExtension')->willReturn(false);
        $container->expects($this->never())->method('prependExtensionConfig');
        $extension = new FilterExtension();
        $extension->prepend($container);
    }

    public function testPrependWithTwig(): void
    {
        /** @var ContainerBuilder&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects($this->once())->method('hasExtension')->willReturn(true);
        $container->expects($this->once())->method('prependExtensionConfig');
        $extension = new FilterExtension();
        $extension->prepend($container);
    }
}

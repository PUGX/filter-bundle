<?php

namespace PUGX\FilterBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use PUGX\FilterBundle\DependencyInjection\FilterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FilterExtensionTest extends TestCase
{
    public function testLoadSetParameters(): void
    {
        /** @var ContainerBuilder&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $extension = new FilterExtension();
        $extension->load([], $container);
        self::assertTrue(true);
    }

    public function testPrependWithoutTwig(): void
    {
        /** @var ContainerBuilder&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('hasExtension')->willReturn(false);
        $container->expects(self::never())->method('prependExtensionConfig');
        $extension = new FilterExtension();
        $extension->prepend($container);
    }

    public function testPrependWithTwig(): void
    {
        /** @var ContainerBuilder&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects(self::once())->method('hasExtension')->willReturn(true);
        $container->expects(self::once())->method('prependExtensionConfig');
        $extension = new FilterExtension();
        $extension->prepend($container);
    }
}

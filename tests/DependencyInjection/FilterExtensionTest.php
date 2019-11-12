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
        $this->assertTrue(true);
    }

    public function testPrependWithoutTwig(): void
    {
        /** @var ContainerBuilder&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('hasExtension')->willReturn(false);
        $container->expects($this->never())->method('prependExtensionConfig')->willReturn(true);
        $extension = new FilterExtension();
        $extension->prepend($container);
    }

    public function testPrependWithTwig(): void
    {
        /** @var ContainerBuilder&\PHPUnit\Framework\MockObject\MockObject $container */
        $container = $this->getMockBuilder(ContainerBuilder::class)->disableOriginalConstructor()->getMock();
        $container->expects($this->once())->method('hasExtension')->willReturn(true);
        $container->expects($this->once())->method('prependExtensionConfig')->willReturn(true);
        $extension = new FilterExtension();
        $extension->prepend($container);
    }
}

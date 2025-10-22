<?php

namespace PUGX\FilterBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

final class FilterExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('twig')) {
            return;
        }
        $reflection = new \ReflectionClass(__CLASS__);
        $path = \dirname($reflection->getFileName()).'/../../templates';
        $container->prependExtensionConfig('twig', ['paths' => [$path]]);
    }
}

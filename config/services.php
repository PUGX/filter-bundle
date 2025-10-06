<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use PUGX\FilterBundle\Filter;
use PUGX\FilterBundle\Twig\FilterRuntime;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->set(Filter::class)
        ->arg('$formFactory', service('form.factory'))
        ->arg('$requestStack', service('request_stack'))
    ;

    $services->set(\PUGX\FilterBundle\Twig\Filter::class)
        ->tag('twig.extension')
    ;

    $services->set(FilterRuntime::class)
        ->arg('$requestStack', service('request_stack'))
        ->arg('$filter', service(Filter::class))
        ->tag('twig.runtime')
    ;
};

<?php

namespace PUGX\FilterBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class Filter extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('filter_has', [FilterRuntime::class, 'has']),
            new TwigFunction('filter_is', [FilterRuntime::class, 'isSet']),
            new TwigFunction('filter_is_not', [FilterRuntime::class, 'isNotSet']),
       ];
    }
}

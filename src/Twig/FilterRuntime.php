<?php

namespace PUGX\FilterBundle\Twig;

use PUGX\FilterBundle\Filter as PFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

final class FilterRuntime implements RuntimeExtensionInterface
{
    public function __construct(private RequestStack $requestStack, private PFilter $filter)
    {
    }

    public function has(string $name): bool
    {
        if (null === $request = $this->requestStack->getMainRequest()) {
            throw new \RuntimeException('No session found.');
        }
        $session = $request->getSession();

        return $session->has('filter.'.$name) && null !== $session->get('filter.'.$name);
    }

    public function isSet(string $prefix, string $name): bool
    {
        $data = $this->filter->filter($prefix);

        return isset($data[$name]);
    }

    public function isNotSet(string $prefix, string $name): bool
    {
        return !$this->isSet($prefix, $name);
    }
}

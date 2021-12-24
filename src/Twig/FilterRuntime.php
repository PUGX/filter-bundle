<?php

namespace PUGX\FilterBundle\Twig;

use PUGX\FilterBundle\Filter as PFilter;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

final class FilterRuntime implements RuntimeExtensionInterface
{
    private RequestStack $requestStack;

    private PFilter $filter;

    public function __construct(RequestStack $requestStack, PFilter $filter)
    {
        $this->requestStack = $requestStack;
        $this->filter = $filter;
    }

    public function has(string $name): bool
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            throw new \RuntimeException('No session found.');
        }
        $session = $request->getSession();

        return $session->has(('filter.'.$name)) && null !== $session->get(('filter.'.$name));
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

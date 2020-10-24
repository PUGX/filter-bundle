<?php

namespace PUGX\FilterBundle\Twig;

use PUGX\FilterBundle\Filter as PFilter;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class FilterRuntime implements RuntimeExtensionInterface
{
    /** @var SessionInterface */
    private $session;

    private PFilter $filter;

    public function __construct(SessionInterface $session, PFilter $filter)
    {
        $this->session = $session;
        $this->filter = $filter;
    }

    public function has(string $name): bool
    {
        return $this->session->has(('filter.'.$name)) && null !== $this->session->get(('filter.'.$name));
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

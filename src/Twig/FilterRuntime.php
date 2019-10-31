<?php

namespace PUGX\FilterBundle\Twig;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class FilterRuntime implements RuntimeExtensionInterface
{
    /** @var SessionInterface */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function has(string $name): bool
    {
        return $this->session->has(('filter.'.$name)) && null !== $this->session->get(('filter.'.$name));
    }
}

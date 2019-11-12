<?php

namespace PUGX\FilterBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use PUGX\FilterBundle\Twig\Filter;

class FilterTest extends TestCase
{
    public function testFunctions(): void
    {
        $filter = new Filter();
        $this->assertIsArray($filter->getFunctions());
    }
}

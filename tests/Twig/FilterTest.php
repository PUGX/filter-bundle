<?php

namespace PUGX\FilterBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use PUGX\FilterBundle\Twig\Filter;

final class FilterTest extends TestCase
{
    public function testFunctions(): void
    {
        $filter = new Filter();
        self::assertIsArray($filter->getFunctions());
    }
}

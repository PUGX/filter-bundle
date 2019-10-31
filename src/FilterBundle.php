<?php

namespace PUGX\FilterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class FilterBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}

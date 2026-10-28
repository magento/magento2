<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SomeModule\Model;

class DoubleColon
{
    public function __construct()
    {
        DoubleColon::class;
    }

    public function method()
    {
    }
}

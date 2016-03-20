<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
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

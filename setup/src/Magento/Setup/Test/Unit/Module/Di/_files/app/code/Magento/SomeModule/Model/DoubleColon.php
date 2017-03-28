<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

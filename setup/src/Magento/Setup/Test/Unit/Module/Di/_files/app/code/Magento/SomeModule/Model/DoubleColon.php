<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
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

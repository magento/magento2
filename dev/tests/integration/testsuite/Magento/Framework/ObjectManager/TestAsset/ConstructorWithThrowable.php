<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\TestAsset;

class ConstructorWithThrowable extends \Magento\Framework\ObjectManager\TestAsset\ConstructorOneArgument
{
    public function __construct(Basic $one)
    {
        // Call parent constructor without parameters to generate TypeError
        parent::__construct();
    }
}
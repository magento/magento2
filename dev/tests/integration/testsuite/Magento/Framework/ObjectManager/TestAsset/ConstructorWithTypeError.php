<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\TestAsset;

class ConstructorWithTypeError
{
    public function __construct()
    {
        // set non-exists property to trigger TypeError
        throw new \TypeError('test error');
    }
}

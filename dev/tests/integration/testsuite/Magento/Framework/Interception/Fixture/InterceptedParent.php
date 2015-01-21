<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Fixture;

class InterceptedParent implements InterceptedParentInterface
{
    public function A($param1)
    {
        return 'A' . $param1 . 'A';
    }

    public function B($param1, $param2)
    {
        return $param1 . $param2 . $this->A($param1);
    }
}

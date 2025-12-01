<?php
/**
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Interception\Fixture;

/**
 * @codingStandardsIgnoreStart
 */
class InterceptedParent implements InterceptedParentInterface
{
    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function A($param1)
    {
        return 'A' . $param1 . 'A';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function B($param1, $param2)
    {
        return $param1 . $param2 . $this->A($param1);
    }
}

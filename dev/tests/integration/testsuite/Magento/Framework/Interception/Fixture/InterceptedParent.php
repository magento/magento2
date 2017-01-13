<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Interception\Fixture;

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

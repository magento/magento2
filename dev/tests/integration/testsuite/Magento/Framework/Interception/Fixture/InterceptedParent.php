<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

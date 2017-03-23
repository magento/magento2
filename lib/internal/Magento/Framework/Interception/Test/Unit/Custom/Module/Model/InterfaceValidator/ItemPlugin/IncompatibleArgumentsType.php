<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin;

class IncompatibleArgumentsType
{
    /**
     * @param \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject
     * @param array $names
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(
        \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject, array $names
    ) {
        return count($names);
    }
}

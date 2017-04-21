<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator\ItemPlugin;

class IncorrectSubject
{
    /**
     * @param \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item $subject
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(\Magento\Framework\Interception\Test\Unit\Custom\Module\Model\Item $subject)
    {
        return true;
    }
}

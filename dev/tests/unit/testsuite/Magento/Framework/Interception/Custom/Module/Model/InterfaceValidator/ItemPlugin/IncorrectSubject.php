<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemPlugin;

class IncorrectSubject
{
    /**
     * @param \Magento\Framework\Interception\Custom\Module\Model\Item $subject
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(\Magento\Framework\Interception\Custom\Module\Model\Item $subject)
    {
        return true;
    }
}

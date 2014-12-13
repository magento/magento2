<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

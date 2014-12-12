<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemPlugin;

class IncompatibleArgumentsType
{
    /**
     * @param \Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject
     * @param array $names
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(
        \Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject, array $names
    ) {
        return count($names);
    }
}

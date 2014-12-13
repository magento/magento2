<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemPlugin;

class ValidPlugin
{
    /**
     * @param \Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItem(
        \Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject, $result
    ) {
        return $result . '!';
    }

    /**
     * @param \Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject
     * @param $name
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItem(
        \Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject, $name
    ) {
        return '|' . $name;
    }

    /**
     * @param \Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject
     * @param Closure $proceed
     * @param string $name
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItem(
        \Magento\Framework\Interception\Custom\Module\Model\InterfaceValidator\ItemWithArguments $subject,
        \Closure $proceed,
        $name
    ) {
        $proceed('&' . $name . '&');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Adminhtml\Order\Create;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * @api
 * @since 100.1.0
 */
class Sidebar
{
    /**
     * Get item qty
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $item
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 100.1.0
     */
    public function aroundGetItemQty(
        \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $item
    ) {
        if ($item->getProduct()->getTypeId() == Configurable::TYPE_CODE) {
            return '';
        }
        return $proceed($item);
    }

    /**
     * Check whether product configuration is required before adding to order
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar $subject
     * @param \Closure $proceed
     * @param string $productType
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 100.1.0
     */
    public function aroundIsConfigurationRequired(
        \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar $subject,
        \Closure $proceed,
        $productType
    ) {
        if ($productType == Configurable::TYPE_CODE) {
            return true;
        }
        return $proceed($productType);
    }
}

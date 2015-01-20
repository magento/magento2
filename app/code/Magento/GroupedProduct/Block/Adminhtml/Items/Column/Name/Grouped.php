<?php
/**
 * Sales Order items name column renderer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Block\Adminhtml\Items\Column\Name;

class Grouped extends \Magento\Sales\Block\Adminhtml\Items\Column\Name
{
    /**
     * Prepare item html
     *
     * This method uses renderer for real product type
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getItem()->getOrderItem()) {
            $item = $this->getItem()->getOrderItem();
        } else {
            $item = $this->getItem();
        }
        if ($productType = $item->getRealProductType()) {
            $renderer = $this->getRenderedBlock()->getColumnHtml($this->getItem(), $productType);
            return $renderer;
        }
        return parent::_toHtml();
    }
}

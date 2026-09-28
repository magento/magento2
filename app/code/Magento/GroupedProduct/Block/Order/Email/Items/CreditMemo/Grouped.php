<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
namespace Magento\GroupedProduct\Block\Order\Email\Items\CreditMemo;

use Magento\Sales\Block\Order\Email\Items\DefaultItems;

/**
 * Class renders grouped product(s) in the CreditMemo email
 *
 * @api
 * @since 100.4.0
 */
class Grouped extends DefaultItems
{
    /**
     * Prepare item html
     *
     * This method uses renderer for real product type
     *
     * @return string
     * @since 100.4.0
     */
    protected function _toHtml()
    {
        if ($this->getItem()->getOrderItem()) {
            $item = $this->getItem()->getOrderItem();
        } else {
            $item = $this->getItem();
        }
        if ($productType = $item->getRealProductType()) {
            $renderer = $this->getRenderedBlock()->getItemRenderer($productType);
            $renderer->setItem($this->getItem());
            return $renderer->toHtml();
        }
        return parent::_toHtml();
    }
}

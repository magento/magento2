<?php
/**
 * Order item render block for grouped product type
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Block\Order\Item\Renderer;

use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;

/**
 * @api
 * @since 2.0.0
 */
class Grouped extends DefaultRenderer
{
    /**
     * Prepare item html
     *
     * This method uses renderer for real product type
     *
     * @return string
     * @since 2.0.0
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

<?php
/**
 * Order item render block for grouped product type
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GroupedProduct\Block\Order\Item\Renderer;

use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;

class Grouped extends DefaultRenderer
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
            $renderer = $this->getRenderedBlock()->getItemRenderer($productType);
            $renderer->setItem($this->getItem());
            return $renderer->toHtml();
        }
        return parent::_toHtml();
    }
}

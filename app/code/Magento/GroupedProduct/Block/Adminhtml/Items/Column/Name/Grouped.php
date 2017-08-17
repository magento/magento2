<?php
/**
 * Sales Order items name column renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Block\Adminhtml\Items\Column\Name;

/**
 * @api
 */
class Grouped extends \Magento\Sales\Block\Adminhtml\Items\Column\Name
{
    const COLUMN_NAME = 'name';

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
            $renderer = $this->getRenderedBlock()->getColumnRenderer(self::COLUMN_NAME, $productType);
            if ($renderer) {
                $renderer->setItem($item);
                $renderer->setField(self::COLUMN_NAME);
                return $renderer->toHtml();
            }
            return '&nbsp;';
        }
        return parent::_toHtml();
    }
}

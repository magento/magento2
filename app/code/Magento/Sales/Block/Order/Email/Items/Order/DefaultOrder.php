<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Order\Email\Items\Order;

use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Sales Order Email items default renderer
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class DefaultOrder extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getItem()->getOrder();
    }

    /**
     * Returns array of Item options
     *
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result[] = $options['options'];
            }
            if (isset($options['additional_options'])) {
                $result[] = $options['additional_options'];
            }
            if (isset($options['attributes_info'])) {
                $result[] = $options['attributes_info'];
            }
        }

        return array_merge([], ...$result);
    }

    /**
     * Formats the value in HTML
     *
     * @param string|array $value
     * @return string
     */
    public function getValueHtml($value)
    {
        if (is_array($value)) {
            return sprintf('%d', $value['qty'])
                . ' x ' . $this->escapeHtml($value['title'])
                . " " . $this->getItem()->getOrder()->formatPrice($value['price']);
        } else {
            return $this->escapeHtml($value);
        }
    }

    /**
     * Returns Product SKU for Item provided
     *
     * @param OrderItem $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku')) {
            return $item->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }

    /**
     * Return product additional information block
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    /**
     * Get the html for item price
     *
     * @param OrderItem $item
     * @return string
     */
    public function getItemPrice(OrderItem $item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $block->setItem($item);
        return $block->toHtml();
    }
}

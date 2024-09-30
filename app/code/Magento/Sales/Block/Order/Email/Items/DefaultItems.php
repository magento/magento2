<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Order\Email\Items;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Sales Order Email items default renderer
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class DefaultItems extends Template
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
     * Returns Items options as array
     *
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        if ($options = $this->getItem()->getOrderItem()->getProductOptions()) {
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
            return sprintf(
                '%d',
                $value['qty']
            ) . ' x ' . $this->escapeHtml(
                $value['title']
            ) . " " . $this->getItem()->getOrder()->formatPrice(
                $value['price']
            );
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
        if ($item->getOrderItem()->getProductOptionByCode('simple_sku')) {
            return $item->getOrderItem()->getProductOptionByCode('simple_sku');
        } else {
            return $item->getSku();
        }
    }

    /**
     * Return product additional information block
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @throws LocalizedException
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    /**
     * Get the html for item price
     *
     * @param OrderItem|InvoiceItem|CreditmemoItem $item
     * @return string
     * @throws LocalizedException
     */
    public function getItemPrice($item)
    {
        $block = $this->getLayout()->getBlock('item_price');
        $item->setRowTotal((float) $item->getPrice() * (float) $this->getItem()->getQty());
        $item->setBaseRowTotal((float) $item->getBasePrice() * (float) $this->getItem()->getQty());
        $block->setItem($item);
        return $block->toHtml();
    }
}

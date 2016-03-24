<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;

/**
 * Base Items block on Credit Memo, Invoice, Shipment view page.
 */
class AbstractItems extends Block
{
    /**
     * Locator for row item.
     *
     * @var string
     */
    protected $rowItem = 'tbody';

    /**
     * Locator for "Product" column.
     *
     * @var string
     */
    protected $product = '.col-product';

    /**
     * Locator for "Price" column.
     *
     * @var string
     */
    protected $price = '.col-price .price';

    /**
     * Locator for "Qty" column.
     *
     * @var string
     */
    protected $qty = '.col-qty';

    /**
     * Locator for "Subtotal" column.
     *
     * @var string
     */
    protected $subtotal = '.col-subtotal .price';

    /**
     * Locator for "Tax Amount" column.
     *
     * @var string
     */
    protected $taxAmount = '.col-tax .price';

    /**
     * Locator for "Discount Amount" column.
     *
     * @var string
     */
    protected $discountAmount = '.col-discount .price';

    /**
     * Locator for "Row total" column.
     *
     * @var string
     */
    protected $rowTotal = '.col-total .price';

    /**
     * Get items data.
     *
     * @return array
     */
    public function getData()
    {
        $items = $this->_rootElement->getElements($this->rowItem);
        $data = [];

        foreach ($items as $item) {
            $itemData = [];

            $itemData += $this->parseProductName($item->find($this->product)->getText());
            $itemData['price'] = $this->escapePrice($item->find($this->price)->getText());
            $itemData['qty'] = $item->find($this->qty)->getText();
            $itemData['subtotal'] = $this->escapePrice($item->find($this->subtotal)->getText());
            $itemData['tax'] = $this->escapePrice($item->find($this->taxAmount)->getText());
            $itemData['discount'] = $this->escapePrice($item->find($this->discountAmount)->getText());
            $itemData['total'] = $this->escapePrice($item->find($this->rowTotal)->getText());

            $data[] = $itemData;
        }

        return $data;
    }

    /**
     * Parse product name to title and sku product.
     *
     * @param string $product
     * @return array
     */
    protected function parseProductName($product)
    {
        $data = array_map('trim', explode('SKU:', str_replace("\n", '', $product)));
        return [
            'product' => $data[0],
            'sku' => isset($data[1]) ? $data[1] : ''
        ];
    }

    /**
     * Prepare price.
     *
     * @var string $price
     * @return string
     */
    protected function escapePrice($price)
    {
        return preg_replace('[^0-9\.]', '', $price);
    }
}

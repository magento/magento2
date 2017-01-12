<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;

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
     * Locator for product sku column.
     *
     * @var string
     */
    protected $sku = '.col-product .product-sku-block';

    /**
     * Locator for product title column.
     *
     * @var string
     */
    protected $title = '.col-product .product-title';

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

            $itemData['product'] = preg_replace('/\n|\r/', '', $item->find($this->title)->getText());
            $itemData['sku'] = $this->getSku($item);
            $itemData['price'] = $this->escapePrice($item->find($this->price)->getText());
            $itemData['qty'] = $this->getQty($item);
            $itemData['subtotal'] = $this->escapePrice($item->find($this->subtotal)->getText());
            $itemData['tax'] = $this->escapePrice($item->find($this->taxAmount)->getText());
            $itemData['discount'] = $this->escapePrice($item->find($this->discountAmount)->getText());
            $itemData['total'] = $this->escapePrice($item->find($this->rowTotal)->getText());

            $data[] = $itemData;
        }

        return $data;
    }

    /**
     * Get product quantity.
     *
     * @param ElementInterface $item
     * @return null|int
     */
    private function getQty(ElementInterface $item)
    {
        $qty = null;
        $elements = $item->getElements($this->qty);
        foreach ($elements as $element) {
            $qty += (int) $element->getText();
        }
        return $qty;
    }

    /**
     * Get product SKU.
     *
     * @param ElementInterface $item
     * @return string
     */
    private function getSku(ElementInterface $item)
    {
        $itemContent = $item->find($this->sku)->getText();
        $itemContent = preg_replace('/\n|\r/', '', $itemContent);
        $itemContent = str_replace('SKU: ', '', $itemContent);
        return $itemContent;
    }

    /**
     * Prepare price.
     *
     * @var string $price
     * @return string
     */
    private function escapePrice($price)
    {
        return preg_replace('[^0-9\.]', '', $price);
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
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\AbstractForm;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Class Product
 * Item product form on items block
 */
class Product extends Form
{
    /**
     * Product price excluding tax search mask
     *
     * @var string
     */
    protected $itemExclTax = '//td[@class="col-price"]/div[@class="price-excl-tax"]/span[@class="price"]';

    /**
     * Product price including tax search mask
     *
     * @var string
     */
    protected $itemInclTax = '//td[@class="col-price"]/div[@class="price-incl-tax"]/span[@class="price"]';

    /**
     * Product price subtotal excluding tax search mask
     *
     * @var string
     */
    protected $itemSubExclTax = '//td[@class="col-subtotal"]/div[@class="price-excl-tax"]/span[@class="price"]';

    /**
     * Product price subtotal including tax search mask
     *
     * @var string
     */
    protected $itemSubInclTax = '//td[@class="col-subtotal"]/div[@class="price-incl-tax"]/span[@class="price"]';

    /**
     * Get Item price excluding tax
     *
     * @return string|null
     */
    public function getItemPriceExclTax()
    {
        $price = $this->_rootElement->find($this->itemExclTax, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item price including tax
     *
     * @return string|null
     */
    public function getItemPriceInclTax()
    {
        $price = $this->_rootElement->find($this->itemInclTax, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item price excluding tax
     *
     * @return string|null
     */
    public function getItemSubExclTax()
    {
        $price = $this->_rootElement->find($this->itemSubExclTax, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Get Item price excluding tax
     *
     * @return string|null
     */

    public function getItemSubInclTax()
    {
        $price = $this->_rootElement->find($this->itemSubInclTax, Locator::SELECTOR_XPATH);
        return $price->isVisible() ? $this->escapeCurrency($price->getText()) : null;
    }

    /**
     * Method that escapes currency symbols
     *
     * @param string $price
     * @return string|null
     */
    protected function escapeCurrency($price)
    {
        preg_match("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", $price, $matches);
        return (isset($matches[1])) ? $matches[1] : null;
    }

    /**
     * Fill item product data
     *
     * @param array $data
     * @return void
     */
    public function fillProduct(array $data)
    {
        $data = $this->dataMapping($data);
        $this->_fill($data);
    }
}

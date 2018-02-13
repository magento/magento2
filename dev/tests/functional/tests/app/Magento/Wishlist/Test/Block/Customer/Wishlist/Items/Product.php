<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Block\Customer\Wishlist\Items;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Class Product
 * Wish List item Product form
 */
class Product extends Form
{
    /**
     * Locator value for "Add to Cart" button.
     *
     * @var string
     */
    protected $addToCart = '.action.tocart';

    /**
     * Locator value for "Remove item" button.
     *
     * @var string
     */
    protected $remove = '[data-role="remove"]';

    /**
     * Locator value for "See Details" tooltip.
     *
     * @var string
     */
    protected $viewDetails = '.details.tooltip';

    /**
     * Locator value for "Details" block.
     *
     * @var string
     */
    protected $detailsBlock = '.product-item-tooltip';

    /**
     * Locator value for "Edit" button.
     *
     * @var string
     */
    protected $edit = '.action.edit';

    /**
     * Locator value for option's label.
     *
     * @var string
     */
    protected $optionLabel = '.tooltip.content .label';

    /**
     * Locator value for option's value.
     *
     * @var string
     */
    protected $optionValue = '.tooltip.content .values';

    /**
     * Locator value for Footer block.
     *
     * @var string
     */
    protected $footer = './ancestor::body//footer';

    /**
     * Locator value for item Price.
     *
     * @var string
     */
    protected $price = '.price';

    /**
     * Locator value for item Price in Product Grid.
     *
     * @var string
     */
    protected $priceInGrid = '.products-grid .price';

    /**
     * Locator value for item Regular Price.
     *
     * @var string
     */
    private $regularPrice = '.old-price [data-price-type="oldPrice"] .price';

    /**
     * Locator value for item Regular Price Label.
     *
     * @var string
     */
    private $regularPriceLabel = '.old-price .price-label';

    /**
     * Fill item with details.
     *
     * @param array $fields
     * @return void
     */
    public function fillProduct(array $fields)
    {
        $this->hoverProductBlock();
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
    }

    /**
     * Click "Add to Cart" button.
     *
     * @return void
     */
    public function clickAddToCart()
    {
        $this->hoverProductBlock();
        $this->_rootElement->find($this->addToCart)->click();
    }

    /**
     * Remove item from Wish List.
     *
     * @return void
     */
    public function remove()
    {
        $this->hoverProductBlock();
        $this->_rootElement->find($this->remove)->click();
    }

    /**
     * Get Product options.
     *
     * @return array|null
     */
    public function getOptions()
    {
        $viewDetails = $this->_rootElement->find($this->viewDetails);
        if ($viewDetails->isVisible()) {
            $this->_rootElement->find($this->footer, Locator::SELECTOR_XPATH)->click();
            $viewDetails->hover();
            $labels = $this->_rootElement->getElements($this->optionLabel);
            $values = $this->_rootElement->getElements($this->optionValue);
            $data = [];
            foreach ($labels as $key => $label) {
                $viewDetails->hover();
                $data[] = [
                    'title' => $label->getText(),
                    'value' => str_replace('$', '', $values[$key]->getText()),
                ];
            }

            return $data;
        } else {
            return null;
        }
    }

    /**
     * Click "Edit" button.
     *
     * @return void
     */
    public function clickEdit()
    {
        $this->hoverProductBlock();
        $this->_rootElement->find($this->edit)->click();
    }

    /**
     * Hover Product block so that possible actions appear.
     *
     * @return void
     */
    public function hoverProductBlock()
    {
        $this->_rootElement->find($this->priceInGrid)->hover();
    }

    /**
     * Returns product price
     *
     * @param string $currency
     * @return string
     */
    public function getPrice($currency = '$')
    {
        return $this->getPriceBySelector($this->price, $currency);
    }

    /**
     * Returns product regular price.
     *
     * @param string $currency
     * @return string
     */
    public function getRegularPrice($currency = '$')
    {
        return $this->getPriceBySelector($this->regularPrice, $currency);
    }

    /**
     * Returns product price by selector.
     *
     * @param string $selector
     * @param string $currency
     * @return string
     */
    private function getPriceBySelector(string $selector, $currency = '$')
    {
        $price = $this->_rootElement->find($selector)->getText();
        return str_replace($currency, '', $price);
    }

    /**
     * Returns product regular price label.
     *
     * @return string
     */
    public function getPriceLabel()
    {
        return (string)$this->_rootElement->find($this->regularPriceLabel)->getText();
    }

    /**
     * Get Wish List data for the Product.
     *
     * @param mixed $qty
     * @return array
     */
    public function getWishlistData($qty = null)
    {
        $this->hoverProductBlock();
        $mapping = $this->dataMapping();
        if (!is_numeric($qty)) {
            unset($mapping['qty']);
        }
        return $this->_getData($mapping);
    }
}

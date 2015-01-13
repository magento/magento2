<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Block\Customer\Wishlist\Items;

use Mtf\Block\Form;
use Mtf\Client\Element\Locator;

/**
 * Class Product
 * Wishlist item product form
 */
class Product extends Form
{
    /**
     * Selector for 'Add to Cart' button
     *
     * @var string
     */
    protected $addToCart = '.action.tocart';

    /**
     * Selector for 'Remove item' button
     *
     * @var string
     */
    protected $remove = '[data-role="remove"]';

    /**
     * Selector for 'View Details' element
     *
     * @var string
     */
    protected $viewDetails = '.details.tooltip';

    /**
     * Selector for 'Details block' element
     *
     * @var string
     */
    protected $detailsBlock = '.product-item-tooltip';

    /**
     * Edit button css selector
     *
     * @var string
     */
    protected $edit = '.action.edit';

    /**
     * Selector for option's label
     *
     * @var string
     */
    protected $optionLabel = '.tooltip.content .label';

    /**
     * Selector for option's value
     *
     * @var string
     */
    protected $optionValue = '.tooltip.content .values';

    /**
     * Selector for click on footer block
     *
     * @var string
     */
    protected $footer = './ancestor::body//footer';

    /**
     * Fill item product details
     *
     * @param array $fields
     * @return void
     */
    public function fillProduct(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
    }

    /**
     * Click button 'Add To Cart'
     *
     * @return void
     */
    public function clickAddToCart()
    {
        $this->_rootElement->find($this->addToCart)->click();
    }

    /**
     * Remove product from wish list
     *
     * @return void
     */
    public function remove()
    {
        $this->_rootElement->find($this->remove)->click();
    }

    /**
     * Get product options
     *
     * @return array|null
     */
    public function getOptions()
    {
        $viewDetails = $this->_rootElement->find($this->viewDetails);
        if ($viewDetails->isVisible()) {
            $this->_rootElement->find($this->footer, Locator::SELECTOR_XPATH)->click();
            $viewDetails->click();
            $labels = $this->_rootElement->find($this->optionLabel)->getElements();
            $values = $this->_rootElement->find($this->optionValue)->getElements();
            $data = [];
            foreach ($labels as $key => $label) {
                if (!$label->isVisible()) {
                    $viewDetails->click();
                }
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
     * Click edit button
     *
     * @return void
     */
    public function clickEdit()
    {
        $this->_rootElement->find($this->edit)->click();
    }
}

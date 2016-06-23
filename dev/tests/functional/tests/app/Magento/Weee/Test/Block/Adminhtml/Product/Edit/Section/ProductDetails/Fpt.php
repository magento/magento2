<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * 'Fixed Product Tax' form.
 */
class Fpt extends SimpleElement
{
    /**
     * 'Add Fixed Product Tax' button selector.
     *
     * @var string
     */
    private $buttonFormLocator = '[data-action="add_new_row"]';

    /**
     * Locator for country.
     *
     * @var string
     */
    private $country = '[name$="[country]"]';

    /**
     * Locator for tax.
     *
     * @var string
     */
    private $tax = '[name$="[value]"]';

    /**
     * Locator for website id.
     *
     * @var string
     */
    private $website = '[name$="[website_id]"]';

    /**
     * Locator for state.
     *
     * @var string
     */
    private $state = '[name$="[state]"]';

    /**
     * Fill Fixed Product Tax form.
     *
     * @param array $value
     * @return void
     */
    public function setValue($value)
    {
        if ($this->find($this->buttonFormLocator)->isVisible()) {
            $this->find($this->buttonFormLocator)->click();
        }
        $countryElement = $this->find($this->country, Locator::SELECTOR_CSS, 'select');
        if ($countryElement->isVisible()) {
            $countryElement->setValue($value['country']);
        }
        $taxElement = $this->find($this->tax, Locator::SELECTOR_CSS, 'input');
        if ($taxElement->isVisible()) {
            $taxElement->setValue($value['tax']);
        }
        $websiteElement = $this->find($this->website, Locator::SELECTOR_CSS, 'select');
        if ($websiteElement->isVisible()) {
            $websiteElement->setValue($value['website']);
        }
        $stateElement = $this->find($this->state, Locator::SELECTOR_CSS, 'select');
        if ($stateElement->isVisible()) {
            $stateElement->setValue($value['state']);
        }
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Checkout\Test\Fixture\Checkout;
use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Shipping
 * One page checkout status shipping block
 *
 */
class Shipping extends Form
{
    /**
     * Continue checkout button
     *
     * @var string
     */
    protected $continue = '#shipping-buttons-container button';

    /**
     * Wait element
     *
     * @var string
     */
    protected $waitElement = '.loading-mask';

    /**
     * Fill form data. Unset 'email' field as it absent in current form
     *
     * @param array $fields
     * @param Element $element
     * @return void
     */
    protected function _fill(array $fields, Element $element = null)
    {
        unset($fields['email']);
        parent::_fill($fields, $element);
    }

    /**
     * Fill shipping address
     *
     * @param Checkout $fixture
     * @return void
     */
    public function fillShipping(Checkout $fixture)
    {
        $shippingAddress = $fixture->getShippingAddress();
        if (!$shippingAddress) {
            return;
        }
        $this->fill($shippingAddress);
        $this->_rootElement->find($this->continue, Locator::SELECTOR_CSS)->click();
        $this->waitForElementNotVisible($this->waitElement);
    }
}

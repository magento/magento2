<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Checkout\Test\Fixture\Checkout;

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
     * Fill shipping address
     *
     * @param $fixture
     * @return void
     */
    public function fillShipping($fixture)
    {
        if ($fixture instanceof Checkout) {
            $fixture = $fixture->getShippingAddress();
        }
        if (!$fixture) {
            return;
        }
        $this->fill($fixture);
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Form;

/**
 * Checkout shipping address block.
 */
class Shipping extends Form
{
    /**
     * Returns form's required elements
     *
     * @return \Magento\Mtf\Client\ElementInterface[]
     */
    public function getRequiredFields()
    {
        return $this->_rootElement->getElements("div .field._required");
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Form;

/**
 * Checkout new shipping address popup block.
 */
class ShippingPopup extends Form
{
    /**
     * Save address button selector.
     *
     * @var string
     */
    private $saveAddressButton = '.action-save-address';

    /**
     * Click on save address button.
     *
     * @return void
     */
    public function clickSaveAddressButton()
    {
        $this->browser->find($this->saveAddressButton)->click();
    }
}

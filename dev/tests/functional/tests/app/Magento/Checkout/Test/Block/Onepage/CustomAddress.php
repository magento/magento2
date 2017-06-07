<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Form for custom billing address filling.
 */
class CustomAddress extends Form
{
    /**
     * Update billing address button.
     *
     * @var string
     */
    private $updateButtonSelector = '.action.action-update';

    /**
     * Select address dropdown.
     *
     * @var string
     */
    private $select = "[name='billing_address_id']";

    /**
     * Click update on billing information block.
     *
     * @return void
     */
    public function clickUpdate()
    {
        $this->_rootElement->find($this->updateButtonSelector)->click();
    }

    /**
     * Set address value from dropdown.
     *
     * @param string $value
     * @return void
     */
    public function selectAddress($value)
    {
        $this->_rootElement->find($this->select, Locator::SELECTOR_CSS, 'select')->setValue($value);
    }
}

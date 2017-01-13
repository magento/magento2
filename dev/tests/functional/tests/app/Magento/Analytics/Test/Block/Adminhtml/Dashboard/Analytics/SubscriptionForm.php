<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\Adminhtml\Dashboard\Analytics;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Create new category.
 */
class SubscriptionForm extends Form
{
    /**
     * Modal checkbox
     *
     * @var string
     */
    private $checkbox = '.admin__control-checkbox';

    /**
     * Enable checkbox in modal window.
     *
     * @return void
     */
    public function enableCheckbox()
    {
        $this->_rootElement->find($this->checkbox, $strategy = Locator::SELECTOR_CSS, 'checkbox')->setValue([1]);
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\Adminhtml\Dashboard\Analytics;

use Magento\Ui\Test\Block\Adminhtml\Modal;
use Magento\Mtf\Client\Locator;

/**
 * Subscription block.
 */
class SubscriptionBlock extends Modal
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
        $this->_rootElement->find($this->checkbox, Locator::SELECTOR_CSS, 'checkbox')->setValue('Yes');
    }

    /**
     * Disable checkbox in modal window.
     *
     * @return void
     */
    public function disableCheckbox()
    {
        $this->_rootElement->find($this->checkbox, Locator::SELECTOR_CSS, 'checkbox')->setValue('No');
    }
}

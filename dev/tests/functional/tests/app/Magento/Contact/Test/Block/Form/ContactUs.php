<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Contact\Test\Block\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Form for "Contact Us" page with captcha.
 */
class ContactUs extends Form
{
    /**
     * Submit form button.
     *
     * @var string
     */
    private $submit = '.action.submit';

    /**
     * Click submit button.
     *
     * @return void
     */
    public function sendComment()
    {
        $this->_rootElement->find($this->submit, Locator::SELECTOR_CSS)->click();
    }
}

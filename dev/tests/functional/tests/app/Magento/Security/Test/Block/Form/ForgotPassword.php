<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Block\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * User forgot password form.
 */
class ForgotPassword extends Form
{
    /**
     * 'Retrieve password' button.
     *
     * @var string
     */
    protected $submit = '.action-retrieve';

    public function submit()
    {
        $this->_rootElement->find($this->submit, Locator::SELECTOR_CSS)->click();
    }
}

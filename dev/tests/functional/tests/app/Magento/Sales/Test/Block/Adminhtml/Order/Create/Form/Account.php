<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Class Account
 * Adminhtml sales order account information block
 *
 */
class Account extends Form
{
    /**
     * Field for customer email
     *
     * @var string
     */
    protected $email = '#email';

    /**
     * Fill in email address
     *
     * @param string $emailAddress
     * @return void
     */
    public function fillEmailAddress($emailAddress)
    {
        $this->_rootElement->find($this->email, Locator::SELECTOR_CSS, 'input')->setValue($emailAddress);
    }
}

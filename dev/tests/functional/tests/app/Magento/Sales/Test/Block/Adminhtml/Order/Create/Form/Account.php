<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml sales order account information block
 */
class Account extends Form
{
    /**
     * Field for customer email
     *
     * @var string
     */
    protected $email = '#email';
}

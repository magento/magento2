<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Block for information about customer on order page
 *
 */
class Info extends Block
{
    /**
     * Customer email
     *
     * @var string
     */
    protected $email = '//th[text()="Email"]/following-sibling::*/a';

    /**
     * Customer group
     *
     * @var string
     */
    protected $group = '//th[text()="Customer Group"]/following-sibling::*';

    /**
     * Get email from the data inside block
     *
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->_rootElement->find($this->email, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get group from the data inside block
     *
     * @return string
     */
    public function getCustomerGroup()
    {
        return $this->_rootElement->find($this->group, Locator::SELECTOR_XPATH)->getText();
    }
}

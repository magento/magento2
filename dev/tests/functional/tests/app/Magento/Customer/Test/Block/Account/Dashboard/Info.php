<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Account\Dashboard;

use Magento\Mtf\Block\Block;

/**
 * Class Info
 * Main block on customer account page
 */
class Info extends Block
{
    /**
     * Css selector for Contact Information Edit Link
     *
     * @var string
     */
    protected $contactInfoEditLink = '.block-dashboard-info .box-information .action.edit';

    /**
     * Css selector for Contact Information Change Password Link
     *
     * @var string
     */
    protected $contactInfoChangePasswordLink = '.block-dashboard-info .box-information .action.change-password';

    /**
     * Dashboard Welcome block locator
     *
     * @var string
     */
    protected $dashboardWelcome = '.block-dashboard-welcome .block-title';

    /**
     * Click on Contact Information Edit Link
     *
     * @return void
     */
    public function openEditContactInfo()
    {
        $this->_rootElement->find($this->contactInfoEditLink)->click();
    }

    /**
     * Click on Contact Information Edit Link
     *
     * @return void
     */
    public function openChangePassword()
    {
        $this->_rootElement->find($this->contactInfoChangePasswordLink)->click();
    }

    /**
     * Get welcome text
     *
     * @return string
     */
    public function getWelcomeText()
    {
        return $this->_rootElement->find($this->dashboardWelcome)->getText();
    }
}

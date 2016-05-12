<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Account\Dashboard;

use Magento\Mtf\Block\Block;

/**
 * Main block on customer account page.
 */
class Info extends Block
{
    /**
     * Css selector for Contact Information Edit Link.
     *
     * @var string
     */
    protected $contactInfoEditLink = '.block-dashboard-info .box-information .action.edit';

    /**
     * Css selector for Contact Information box content.
     *
     * @var string
     */
    protected $contactInfoBoxContent = '.box.box-information .box-content';

    /**
     * Css selector for Contact Information Change Password Link.
     *
     * @var string
     */
    protected $contactInfoChangePasswordLink = '.block-dashboard-info .box-information .action.change-password';

    /**
     * Css selector for Contact Information Change Email Link.
     *
     * @var string
     */
    protected $contactInfoChangeEmailLink = '.form-edit-account .fieldset.info .checkbox#change-email';

    /**
     * Css selector for Contact Information Change Password Checkbox.
     *
     * @var string
     */
    protected $contactInfoChangePasswordCheckbox = '.form-edit-account .fieldset.info .checkbox#change-password';

    /**
     * Dashboard Welcome block locator.
     *
     * @var string
     */
    protected $dashboardWelcome = '.block-dashboard-welcome .block-title';

    /**
     * Click on Contact Information Edit Link.
     *
     * @return void
     */
    public function openEditContactInfo()
    {
        $this->_rootElement->find($this->contactInfoEditLink)->click();
        $this->openChangeEmail();
    }

    /**
     * Click on Contact Information Edit Link.
     *
     * @return void
     */
    public function openChangePassword()
    {
        $this->_rootElement->find($this->contactInfoChangePasswordLink)->click();
    }

    /**
     * Get welcome text.
     *
     * @return string
     */
    public function getWelcomeText()
    {
        return $this->_rootElement->find($this->dashboardWelcome)->getText();
    }

    /**
     * Click on Change Email checkbox.
     *
     * @return void
     */
    public function openChangeEmail()
    {
        $this->_rootElement->find($this->contactInfoChangeEmailLink)->click();
    }

    /**
     * Click on Change Password checkbox.
     *
     * @return void
     */
    public function checkChangePassword()
    {
        $this->_rootElement->find($this->contactInfoChangePasswordCheckbox)->click();
    }

    /**
     * Get Contact Information block content.
     *
     * @return string
     */
    public function getContactInfoContent()
    {
        return $this->_rootElement->find($this->contactInfoBoxContent)->getText();
    }
}

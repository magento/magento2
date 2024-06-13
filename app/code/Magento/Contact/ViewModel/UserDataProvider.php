<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

namespace Magento\Contact\ViewModel;

use Magento\Contact\Helper\Data;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Provides the user data to fill the form.
 */
class UserDataProvider implements ArgumentInterface
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * UserDataProvider constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get user name
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->helper->getPostValue('name') ?: $this->helper->getUserName();
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->helper->getPostValue('email') ?: $this->helper->getUserEmail();
    }

    /**
     * Get user telephone
     *
     * @return string
     */
    public function getUserTelephone()
    {
        return $this->helper->getPostValue('telephone');
    }

    /**
     * Get user comment
     *
     * @return string
     */
    public function getUserComment()
    {
        return $this->helper->getPostValue('comment');
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Helper\AccountManagement as AccountManagementHelper;

/**
 * Customer reset password form
 */
class Resetpassword extends \Magento\Framework\View\Element\Template
{
    /**
     * @var AccountManagementHelper
     */
    protected $accountManagementHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param AccountManagementHelper $accountManagementHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        AccountManagementHelper $accountManagementHelper,
        array $data = []
    ) {
        $this->accountManagementHelper = $accountManagementHelper;
        parent::__construct($context, $data);
    }



    /**
     * Check if autocomplete is disabled on storefront
     *
     * @return bool
     */
    public function isAutocompleteDisabled()
    {
        return (bool)!$this->_scopeConfig->getValue(
            \Magento\Customer\Model\Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get minimum password length
     *
     * @return string
     */
    public function getMinimumPasswordLength()
    {
        return $this->accountManagementHelper->getMinimumPasswordLength();
    }

    /**
     * Get minimum password length
     *
     * @return string
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->accountManagementHelper->getRequiredCharacterClassesNumber();
    }
}

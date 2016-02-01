<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Helper\Config as CustomerConfigHelper;

/**
 * Customer reset password form
 */
class Resetpassword extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CustomerConfigHelper
     */
    protected $customerConfigHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerConfigHelper $customerConfigHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerConfigHelper $customerConfigHelper,
        array $data = []
    ) {
        $this->customerConfigHelper = $customerConfigHelper;
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
        return $this->customerConfigHelper->getMinimumPasswordLength();
    }

    /**
     * Get minimum password length
     *
     * @return string
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->customerConfigHelper->getRequiredCharacterClassesNumber();
    }
}

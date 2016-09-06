<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Model\Form;
use Magento\Store\Model\ScopeInterface;

class AuthenticationPopup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->jsLayout);
    }

    /**
     * Returns popup config
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'autocomplete' => $this->escapeHtml($this->isAutocompleteEnabled()),
            'customerRegisterUrl' => $this->escapeUrl($this->getCustomerRegisterUrlUrl()),
            'customerForgotPasswordUrl' => $this->escapeUrl($this->getCustomerForgotPasswordUrl()),
            'baseUrl' => $this->escapeUrl($this->getBaseUrl())
        ];
    }

    /**
     * Is autocomplete enabled for storefront
     *
     * @return string
     */
    private function isAutocompleteEnabled()
    {
        return $this->_scopeConfig->getValue(
            Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            ScopeInterface::SCOPE_STORE
        ) ? 'on' : 'off';
    }

    /**
     * Return base url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get customer register url
     *
     * @return string
     */
    public function getCustomerRegisterUrlUrl()
    {
        return $this->getUrl('customer/account/create');
    }

    /**
     * Get customer forgot password url
     *
     * @return string
     */
    public function getCustomerForgotPasswordUrl()
    {
        return $this->getUrl('customer/account/forgotpassword');
    }
}

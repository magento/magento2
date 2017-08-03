<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Model\Form;
use Magento\Store\Model\ScopeInterface;

/**
 * @api
 */
class AuthenticationPopup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        return $this->serializer->serialize($this->jsLayout);
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
     * Returns popup config in JSON format.
     *
     * Added in scope of https://github.com/magento/magento2/pull/8617
     *
     * @return bool|string
     * @since 2.2.0
     */
    public function getSerializedConfig()
    {
        return $this->serializer->serialize($this->getConfig());
    }

    /**
     * Is autocomplete enabled for storefront
     *
     * @return string
     * @since 2.2.0
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

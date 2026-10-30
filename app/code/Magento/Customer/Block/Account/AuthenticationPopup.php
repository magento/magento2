<?php
/**
 * Copyright 2015 Adobe.
 * All Rights Reserved.
 */
namespace Magento\Customer\Block\Account;

use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Form;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * @api
 * @since 100.0.2
 */
class AuthenticationPopup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var array
     */
    private $layoutProcessors;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param array $layoutProcessors
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null,
        array $layoutProcessors = []
    ) {
        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->layoutProcessors = $layoutProcessors;
    }

    /**
     *  Returns serialize jsLayout
     *
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
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
            'baseUrl' => $this->escapeUrl($this->getBaseUrl()),
            'customerLoginUrl' => $this->getUrl('customer/ajax/login'),
        ];
    }

    /**
     * Returns popup config in JSON format.
     *
     * Added in scope of https://github.com/magento/magento2/pull/8617
     *
     * @return bool|string
     * @since 101.0.0
     */
    public function getSerializedConfig()
    {
        return $this->serializer->serialize($this->getConfig());
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

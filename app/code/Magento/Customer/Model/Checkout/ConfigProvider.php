<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Url;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Form;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     * @deprecated 101.0.4
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Url
     */
    private $customerUrl;

    /**
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Url|null $customerUrl
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Url $customerUrl = null
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerUrl = $customerUrl ?? ObjectManager::getInstance()
                ->get(Url::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'customerLoginUrl' => $this->getLoginUrl(),
            'isRedirectRequired' => $this->isRedirectRequired(),
            'autocomplete' => $this->isAutocompleteEnabled(),
        ];
    }

    /**
     * Is autocomplete enabled for storefront
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected function isAutocompleteEnabled()
    {
        return $this->scopeConfig->getValue(
            Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            ScopeInterface::SCOPE_STORE
        ) ? 'on' : 'off';
    }

    /**
     * Returns URL to login controller action
     *
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }

    /**
     * Whether redirect to login page is required
     *
     * @return bool
     */
    protected function isRedirectRequired()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        if (strpos($this->getLoginUrl(), $baseUrl) !== false) {
            return false;
        }

        return true;
    }
}

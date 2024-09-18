<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Currency;

use Magento\Store\Model\Store;

class DefaultLocator
{
    /**
     * Config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_configuration;

    /**
     * Store manager model
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configuration
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configuration,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_configuration = $configuration;
        $this->_storeManager = $storeManager;
    }

    /**
     * Retrieve default currency for selected store, website or website group
     *
     * @todo: Refactor to ScopeDefiner
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    public function getDefaultCurrency(\Magento\Framework\App\RequestInterface $request)
    {
        if ($request->getParam('store')) {
            $store = $request->getParam('store');
            $currencyCode = $this->_storeManager->getStore($store)->getBaseCurrencyCode();
        } else {
            if ($request->getParam('website')) {
                $website = $request->getParam('website');
                $currencyCode = $this->_storeManager->getWebsite($website)->getBaseCurrencyCode();
            } else {
                if ($request->getParam('group')) {
                    $group = $request->getParam('group');
                    $currencyCode = $this->_storeManager->getGroup($group)->getWebsite()->getBaseCurrencyCode();
                } else {
                    $currencyCode = $this->_configuration->getValue(
                        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                        'default'
                    );
                }
            }
        }

        return $currencyCode;
    }

    /**
     * Retrieve display currency for selected store, website or website group
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    public function getDisplayCurrency(\Magento\Framework\App\RequestInterface $request): string
    {
        if ($request->getParam('store')) {
            $store = $request->getParam('store');
            $currencyCode = $this->_storeManager->getStore($store)->getDefaultCurrencyCode();
        } else {
            if ($request->getParam('website')) {
                $website = $request->getParam('website');
                $currencyCode = $this->getDefaultCurrencyCode($website);
            } else {
                if ($request->getParam('group')) {
                    $group = $request->getParam('group');
                    $currencyCode = $this->_storeManager->getGroup($group)->getWebsite()->getDefaultCurrencyCode();
                } else {
                    $currencyCode = $this->_configuration->getValue(
                        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
                        'default'
                    );
                }
            }
        }

        return $currencyCode;
    }

    /**
     * Retrieve website default currency code
     *
     * @param string $website
     * @return string
     */
    private function getDefaultCurrencyCode($website): string
    {
        $website = $this->_storeManager->getWebsite($website);
        $priceScope = $website->getConfig(Store::XML_PATH_PRICE_SCOPE);
        if ($priceScope == Store::PRICE_SCOPE_GLOBAL) {
            $currencyCode = $this->_configuration->getValue(
                \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
                'default'
            );
        } else {
            $currencyCode = $website->getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT);
        }

        return $currencyCode;
    }
}

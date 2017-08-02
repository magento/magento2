<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Currency dropdown block
 */
namespace Magento\Directory\Block;

use Magento\Framework\Locale\Bundle\CurrencyBundle as CurrencyBundle;

/**
 * @api
 * @since 2.0.0
 */
class Currency extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     * @since 2.0.0
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     * @since 2.0.0
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        array $data = []
    ) {
        $this->_currencyFactory = $currencyFactory;
        $this->_postDataHelper = $postDataHelper;
        parent::__construct($context, $data);
        $this->localeResolver = $localeResolver;
    }

    /**
     * Retrieve count of currencies
     * Return 0 if only one currency
     *
     * @return int
     * @since 2.0.0
     */
    public function getCurrencyCount()
    {
        return count($this->getCurrencies());
    }

    /**
     * Retrieve currencies array
     * Return array: code => currency name
     * Return empty array if only one currency
     *
     * @return array
     * @since 2.0.0
     */
    public function getCurrencies()
    {
        $currencies = $this->getData('currencies');
        if ($currencies === null) {
            $currencies = [];
            $codes = $this->_storeManager->getStore()->getAvailableCurrencyCodes(true);
            if (is_array($codes) && count($codes) > 1) {
                $rates = $this->_currencyFactory->create()->getCurrencyRates(
                    $this->_storeManager->getStore()->getBaseCurrency(),
                    $codes
                );

                foreach ($codes as $code) {
                    if (isset($rates[$code])) {
                        $allCurrencies = (new CurrencyBundle())->get(
                            $this->localeResolver->getLocale()
                        )['Currencies'];
                        $currencies[$code] = $allCurrencies[$code][1] ?: $code;
                    }
                }
            }

            $this->setData('currencies', $currencies);
        }
        return $currencies;
    }

    /**
     * Retrieve Currency Swith URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getSwitchUrl()
    {
        return $this->getUrl('directory/currency/switch');
    }

    /**
     * Return POST data for currency to switch
     *
     * @param string $code
     * @return string
     * @since 2.0.0
     */
    public function getSwitchCurrencyPostData($code)
    {
        return $this->_postDataHelper->getPostData($this->escapeUrl($this->getSwitchUrl()), ['currency' => $code]);
    }

    /**
     * Retrieve Current Currency code
     *
     * @return string
     * @since 2.0.0
     */
    public function getCurrentCurrencyCode()
    {
        if ($this->_getData('current_currency_code') === null) {
            // do not use $this->_storeManager->getStore()->getCurrentCurrencyCode() because of probability
            // to get an invalid (without base rate) currency from code saved in session
            $this->setData('current_currency_code', $this->_storeManager->getStore()->getCurrentCurrency()->getCode());
        }
        return $this->_getData('current_currency_code');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
}

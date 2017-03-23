<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model\Currency\Import;

/**
 * Currency rate import model (From www.webservicex.net)
 */
class Webservicex extends \Magento\Directory\Model\Currency\Import\AbstractImport
{
    /**
     * Currency converter url
     */
    // @codingStandardsIgnoreStart
    const CURRENCY_CONVERTER_URL = 'http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency={{CURRENCY_FROM}}&ToCurrency={{CURRENCY_TO}}';
    // @codingStandardsIgnoreEnd

    /**
     * Http Client Factory
     *
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * Core scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $currencyFrom
     * @param string $currencyTo
     * @param int $retry
     * @return float|null
     */
    protected function _convert($currencyFrom, $currencyTo, $retry = 0)
    {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, self::CURRENCY_CONVERTER_URL);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);
        /** @var \Magento\Framework\HTTP\ZendClient $httpClient */
        $httpClient = $this->getHttpClientFactory()->create();

        try {
            $response = $httpClient->setUri(
                $url
            )->setConfig(
                [
                    'timeout' => $this->scopeConfig->getValue(
                        'currency/webservicex/timeout',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                ]
            )->request(
                'GET'
            )->getBody();

            $xml = simplexml_load_string($response, null, LIBXML_NOERROR);
            if (!$xml || (isset($xml[0]) && $xml[0] == -1)) {
                $this->_messages[] = __('We can\'t retrieve a rate from %1.', $url);
                return null;
            }
            return (double)$xml;
        } catch (\Exception $e) {
            if ($retry == 0) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = __('We can\'t retrieve a rate from %1.', $url);
            }
        }
    }

    /**
     * Get HttpClientFactory dependency
     *
     * @return \Magento\Framework\HTTP\ZendClientFactory
     *
     * @deprecated
     */
    private function getHttpClientFactory()
    {
        if ($this->httpClientFactory === null) {
            $this->httpClientFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\HTTP\ZendClientFactory::class);
        }
        return $this->httpClientFactory;
    }
}

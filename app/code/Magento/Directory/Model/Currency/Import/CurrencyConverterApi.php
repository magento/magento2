<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Model\Currency\Import;

use Magento\Framework\Exception\LocalizedException;

class CurrencyConverterApi extends AbstractImport
{
    /**
     * @var string
     */
    const CURRENCY_CONVERTER_URL = 'http://free.currencyconverterapi.com/api/v6/convert?q={{CURRENCY_FROM}}_{{CURRENCY_TO}}&compact=ultra&apiKey={{API_KEY}}'; //@codingStandardsIgnoreLine

    /**
     * Http Client Factory
     *
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * Core scope config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     */
    public function __construct(
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ) {
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchRates()
    {
        $data = [];
        $currencies = $this->_getCurrencyCodes();
        $defaultCurrencies = $this->_getDefaultCurrencyCodes();

        foreach ($defaultCurrencies as $currencyFrom) {
            if (!isset($data[$currencyFrom])) {
                $data[$currencyFrom] = [];
            }
            $data = $this->convertBatch($data, $currencyFrom, $currencies);
            ksort($data[$currencyFrom]);
        }
        return $data;
    }

    /**
     * Return currencies convert rates in batch mode
     *
     * @param array $data
     * @param string $currencyFrom
     * @param array $currenciesTo
     * @return array
     */
    private function convertBatch($data, $currencyFrom, $currenciesTo)
    {
        $apiKey = $this->scopeConfig->getValue(
            'currency/currencyconverterapi/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($apiKey)) {
            throw new LocalizedException(
                __('No API Key was specified or an invalid API Key was specified.')
            );
        }

        foreach ($currenciesTo as $to) {
            set_time_limit(0);
            try {
                $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, self::CURRENCY_CONVERTER_URL);
                $url = str_replace('{{CURRENCY_TO}}', $to, $url);
                $url = str_replace('{{API_KEY}}', $apiKey, $url);
                $response = $this->getServiceResponse($url);
                if ($currencyFrom == $to) {
                    $data[$currencyFrom][$to] = $this->_numberFormat(1);
                } else {
                    if (empty($response)) {
                        $this->_messages[] = __('We can\'t retrieve a rate from %1 for %2.', $url, $to);
                        $data[$currencyFrom][$to] = null;
                    } else {
                        $data[$currencyFrom][$to] = $this->_numberFormat(
                            (double)$response[$currencyFrom . '_' . $to]
                        );
                    }
                }
            } finally {
                ini_restore('max_execution_time');
            }
        }

        return $data;
    }

    /**
     * Get Fixer.io service response
     *
     * @param string $url
     * @param int $retry
     * @return array
     */
    private function getServiceResponse($url, $retry = 0)
    {
        /** @var \Magento\Framework\HTTP\ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $response = [];

        try {
            $jsonResponse = $httpClient->setUri(
                $url
            )->setConfig(
                [
                    'timeout' => $this->scopeConfig->getValue(
                        'currency/currencyconverterapi/timeout',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                ]
            )->request(
                'GET'
            )->getBody();

            $response = json_decode($jsonResponse, true);
        } catch (\Exception $e) {
            if ($retry == 0) {
                $response = $this->getServiceResponse($url, 1);
            }
        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        return 1;
    }
}

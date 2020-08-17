<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Model\Currency\Import;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Exception;

/**
 * Currency rate converter (free.currconv.com).
 */
class CurrencyConverterApi extends AbstractImport
{
    /**
     * @var string
     */
    public const CURRENCY_CONVERTER_URL = 'https://free.currconv.com/api/v7/convert?apiKey={{ACCESS_KEY}}'
        . '&q={{CURRENCY_RATES}}&compact=ultra';

    /**
     * Http Client Factory
     *
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * Core scope config
     *
     * @var ScopeConfig
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $currencyConverterServiceHost = '';

    /**
     * @var string
     */
    private $serviceUrl = '';

    /**
     * @param CurrencyFactory $currencyFactory
     * @param ScopeConfig $scopeConfig
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        ScopeConfig $scopeConfig,
        ZendClientFactory $httpClientFactory
    ) {
        parent::__construct($currencyFactory);
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * @inheritdoc
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
    private function convertBatch(array $data, string $currencyFrom, array $currenciesTo): array
    {
        $url = $this->getServiceURL($currencyFrom, $currenciesTo);
        if (empty($url)) {
            $data[$currencyFrom] = $this->makeEmptyResponse($currenciesTo);
            return $data;
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        set_time_limit(0);
        try {
            $response = $this->getServiceResponse($url);
        } finally {
            ini_restore('max_execution_time');
        }

        if (!$this->validateResponse($response)) {
            $data[$currencyFrom] = $this->makeEmptyResponse($currenciesTo);
            return $data;
        }

        foreach ($currenciesTo as $to) {
            if ($currencyFrom === $to) {
                $data[$currencyFrom][$to] = $this->_numberFormat(1);
            } else {
                if (!isset($response[$currencyFrom . '_' . $to])) {
                    $serviceHost =  $this->getServiceHost($url);
                    $this->_messages[] = __('We can\'t retrieve a rate from %1 for %2.', $serviceHost, $to);
                    $data[$currencyFrom][$to] = null;
                } else {
                    $data[$currencyFrom][$to] = $this->_numberFormat(
                        (double)$response[$currencyFrom . '_' . $to]
                    );
                }
            }
        }

        return $data;
    }

    /**
     * Get currency converter service host.
     *
     * @param string $url
     * @return string
     */
    private function getServiceHost(string $url): string
    {
        if (!$this->currencyConverterServiceHost) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $this->currencyConverterServiceHost = parse_url($url, PHP_URL_SCHEME) . '://'
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                . parse_url($url, PHP_URL_HOST);
        }
        return $this->currencyConverterServiceHost;
    }

    /**
     * Return service URL.
     *
     * @param string $currencyFrom
     * @param array $currenciesTo
     * @return string
     */
    private function getServiceURL(string $currencyFrom, array $currenciesTo): string
    {
        if (!$this->serviceUrl) {
            // Get access key
            $accessKey = $this->scopeConfig
                ->getValue('currency/currencyconverterapi/api_key', ScopeInterface::SCOPE_STORE);
            if (empty($accessKey)) {
                $this->_messages[] = __('No API Key was specified or an invalid API Key was specified.');
                return '';
            }
            // Get currency rates request
            $currencyQueryParts = [];
            foreach ($currenciesTo as $currencyTo) {
                $currencyQueryParts[] = sprintf('%s_%s', $currencyFrom, $currencyTo);
            }
            $currencyRates = implode(',', $currencyQueryParts);
            $this->serviceUrl = str_replace(
                ['{{ACCESS_KEY}}', '{{CURRENCY_RATES}}'],
                [$accessKey, $currencyRates],
                self::CURRENCY_CONVERTER_URL
            );
        }
        return $this->serviceUrl;
    }

    /**
     * Get Fixer.io service response
     *
     * @param string $url
     * @param int $retry
     * @return array
     */
    private function getServiceResponse($url, $retry = 0): array
    {
        /** @var ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $response = [];

        try {
            $jsonResponse = $httpClient->setUri(
                $url
            )->setConfig(
                [
                    'timeout' => $this->scopeConfig->getValue(
                        'currency/currencyconverterapi/timeout',
                        ScopeInterface::SCOPE_STORE
                    ),
                ]
            )->request(
                'GET'
            )->getBody();

            $response = json_decode($jsonResponse, true) ?: [];
        } catch (Exception $e) {
            if ($retry == 0) {
                $response = $this->getServiceResponse($url, 1);
            }
        }
        return $response;
    }

    /**
     * Validate rates response.
     *
     * @param array $response
     * @return bool
     */
    private function validateResponse(array $response): bool
    {
        if (!isset($response['error'])) {
            return true;
        }
        $this->_messages[] = $response['error'] ?: __('Currency rates can\'t be retrieved.');
        return false;
    }

    /**
     * Make empty rates for provided currencies.
     *
     * @param array $currenciesTo
     * @return array
     */
    private function makeEmptyResponse(array $currenciesTo): array
    {
        return array_fill_keys($currenciesTo, null);
    }

    /**
     * @inheritdoc
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
        return 1;
    }
}

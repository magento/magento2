<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Model\Currency\Import;

use Exception;
use Laminas\Http\Request;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\LaminasClientFactory as HttpClientFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\LaminasClient;

/**
 * Currency rate import model (https://apilayer.com/marketplace/fixer-api)
 */
class FixerIoApiLayer implements ImportInterface
{
    private const CURRENCY_CONVERTER_HOST = 'https://api.apilayer.com';
    private const CURRENCY_CONVERTER_URL_PATH = '/fixer/latest?'
    . 'apikey={{ACCESS_KEY}}&base={{CURRENCY_FROM}}&symbols={{CURRENCY_TO}}';

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var HttpClientFactory
     */
    private $httpClientFactory;

    /**
     * @var CurrencyFactory
     */
    private $currencyFactory;

    /**
     * Core scope config
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Initialize dependencies
     *
     * @param CurrencyFactory $currencyFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param HttpClientFactory $httpClientFactory
     */
    public function __construct(
        CurrencyFactory $currencyFactory,
        ScopeConfigInterface $scopeConfig,
        HttpClientFactory $httpClientFactory
    ) {
        $this->currencyFactory = $currencyFactory;
        $this->scopeConfig = $scopeConfig;
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * Import rates
     *
     * @return $this
     */
    public function importRates()
    {
        $data = $this->fetchRates();
        $this->saveRates($data);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function fetchRates()
    {
        $data = [];
        $currencies = $this->getCurrencyCodes();
        $defaultCurrencies = $this->getDefaultCurrencyCodes();

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
     * @inheritdoc
     */
    public function getMessages()
    {
        return $this->messages;
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
        $accessKey = $this->scopeConfig->getValue('currency/fixerio_apilayer/api_key', ScopeInterface::SCOPE_STORE);
        if (empty($accessKey)) {
            $this->messages[] = __('No API Key was specified or an invalid API Key was specified.');
            $data[$currencyFrom] = $this->makeEmptyResponse($currenciesTo);
            return $data;
        }

        $currenciesStr = implode(',', $currenciesTo);
        $url = str_replace(
            ['{{ACCESS_KEY}}', '{{CURRENCY_FROM}}', '{{CURRENCY_TO}}'],
            [$accessKey, $currencyFrom, $currenciesStr],
            self::CURRENCY_CONVERTER_HOST . self::CURRENCY_CONVERTER_URL_PATH
        );
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        set_time_limit(0);
        try {
            $response = $this->getServiceResponse($url);
        } finally {
            ini_restore('max_execution_time');
        }

        if (!$this->validateResponse($response, $currencyFrom)) {
            $data[$currencyFrom] = $this->makeEmptyResponse($currenciesTo);
            return $data;
        }

        foreach ($currenciesTo as $currencyTo) {
            if ($currencyFrom == $currencyTo) {
                $data[$currencyFrom][$currencyTo] = 1;
            } else {
                if (empty($response['rates'][$currencyTo])) {
                    $message = 'We can\'t retrieve a rate from %1 for %2.';
                    $this->messages[] = __($message, self::CURRENCY_CONVERTER_HOST, $currencyTo);
                    $data[$currencyFrom][$currencyTo] = null;
                } else {
                    $data[$currencyFrom][$currencyTo] = (double)$response['rates'][$currencyTo];
                }
            }
        }
        return $data;
    }

    /**
     * Saving currency rates
     *
     * @param array $rates
     * @return \Magento\Directory\Model\Currency\Import\FixerIoApiLayer
     */
    private function saveRates(array $rates)
    {
        foreach ($rates as $currencyCode => $currencyRates) {
            $this->currencyFactory->create()->setId($currencyCode)->setRates($currencyRates)->save();
        }
        return $this;
    }

    /**
     * Get apilayer.com service response
     *
     * @param string $url
     * @param int $retry
     * @return array
     */
    private function getServiceResponse(string $url, int $retry = 0): array
    {
        /** @var LaminasClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $response = [];

        try {
            $httpClient->setUri($url);
            $httpClient->setOptions(
                [
                    'timeout' => $this->scopeConfig->getValue(
                        'currency/fixerio_apilayer/timeout',
                        ScopeInterface::SCOPE_STORE
                    ),
                ]
            );
            $httpClient->setMethod(Request::METHOD_GET);
            $jsonResponse = $httpClient->send()->getBody();

            $response = json_decode($jsonResponse, true);
        } catch (Exception $e) {
            if ($retry == 0) {
                $response = $this->getServiceResponse($url, 1);
            }
        }
        return $response;
    }

    /**
     * Creates array for provided currencies with empty rates.
     *
     * @param array $currenciesTo
     * @return array
     */
    private function makeEmptyResponse(array $currenciesTo): array
    {
        return array_fill_keys($currenciesTo, null);
    }

    /**
     * Validates rates response.
     *
     * @param array $response
     * @param string $baseCurrency
     * @return bool
     */
    private function validateResponse(array $response, string $baseCurrency): bool
    {
        if ($response['success']) {
            return true;
        }

        $errorCodes = [
            101 => __('No API Key was specified or an invalid API Key was specified.'),
            102 => __('The account this API request is coming from is inactive.'),
            105 => __('The "%1" is not allowed as base currency for your subscription plan.', $baseCurrency),
            201 => __('An invalid base currency has been entered.'),
        ];

        $this->messages[] = $errorCodes[$response['error']['code']] ?? __('Currency rates can\'t be retrieved.');

        return false;
    }

    /**
     * Retrieve currency codes
     *
     * @return array
     */
    private function getCurrencyCodes()
    {
        return $this->currencyFactory->create()->getConfigAllowCurrencies();
    }

    /**
     * Retrieve default currency codes
     *
     * @return array
     */
    private function getDefaultCurrencyCodes()
    {
        return $this->currencyFactory->create()->getConfigBaseCurrencies();
    }
}

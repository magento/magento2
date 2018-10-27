<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Currency\Import;

use Magento\Store\Model\ScopeInterface;

/**
 * Currency rate import model (From http://fixer.io/)
 */
class FixerIo extends AbstractImport
{
    /**
     * @var string
     */
    const CURRENCY_CONVERTER_URL = 'http://data.fixer.io/api/latest?access_key={{ACCESS_KEY}}'
        . '&base={{CURRENCY_FROM}}&symbols={{CURRENCY_TO}}';

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
     * {@inheritdoc}
     */
    protected function _convert($currencyFrom, $currencyTo)
    {
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
        $accessKey = $this->scopeConfig->getValue('currency/fixerio/api_key', ScopeInterface::SCOPE_STORE);
        if (empty($accessKey)) {
            $this->_messages[] = __('No API Key was specified or an invalid API Key was specified.');
            $data[$currencyFrom] = $this->makeEmptyResponse($currenciesTo);
            return $data;
        }

        $currenciesStr = implode(',', $currenciesTo);
        $url = str_replace(
            ['{{ACCESS_KEY}}', '{{CURRENCY_FROM}}', '{{CURRENCY_TO}}'],
            [$accessKey, $currencyFrom, $currenciesStr],
            self::CURRENCY_CONVERTER_URL
        );

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
                $data[$currencyFrom][$currencyTo] = $this->_numberFormat(1);
            } else {
                if (empty($response['rates'][$currencyTo])) {
                    $this->_messages[] = __('We can\'t retrieve a rate from %1 for %2.', $url, $currencyTo);
                    $data[$currencyFrom][$currencyTo] = null;
                } else {
                    $data[$currencyFrom][$currencyTo] = $this->_numberFormat(
                        (double)$response['rates'][$currencyTo]
                    );
                }
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
            $jsonResponse = $httpClient->setUri($url)
                ->setConfig(
                    [
                        'timeout' => $this->scopeConfig->getValue(
                            'currency/fixerio/timeout',
                            ScopeInterface::SCOPE_STORE
                        ),
                    ]
                )
                ->request('GET')
                ->getBody();

            $response = json_decode($jsonResponse, true);
        } catch (\Exception $e) {
            if ($retry == 0) {
                $response = $this->getServiceResponse($url, 1);
            }
        }
        return $response;
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

        $this->_messages[] = $errorCodes[$response['error']['code']] ?? __('Currency rates can\'t be retrieved.');

        return false;
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
}

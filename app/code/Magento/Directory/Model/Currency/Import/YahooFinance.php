<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Currency\Import;

/**
 * Currency rate import model (From http://query.yahooapis.com/)
 */
class YahooFinance extends \Magento\Directory\Model\Currency\Import\AbstractImport
{
    /**
     * Currency converter url string
     *
     * @var string
     */
    // @codingStandardsIgnoreStart
    private $currencyConverterUrl = 'http://query.yahooapis.com/v1/public/yql?format=json&q={{YQL_STRING}}&env=store://datatables.org/alltableswithkeys';
    // @codingStandardsIgnoreEnd

    /**
     * Config path for service timeout
     *
     * @var string
     */
    private $timeoutConfigPath = 'currency/yahoofinance/timeout';

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
     * Return currencies convert rates in batch mode
     *
     * @param array $data
     * @param string $currencyFrom
     * @param array $currenciesTo
     * @return array
     */
    private function convertBatch($data, $currencyFrom, $currenciesTo)
    {
        $url = $this->buildUrl($currencyFrom, $currenciesTo);
        set_time_limit(0);
        try {
            $response = $this->getServiceResponse($url);
        } finally {
            ini_restore('max_execution_time');
        }

        foreach ($currenciesTo as $currencyTo) {
            if ($currencyFrom == $currencyTo) {
                $data[$currencyFrom][$currencyTo] = $this->_numberFormat(1);
            } else {
                if (empty($response[$currencyFrom . $currencyTo])) {
                    $this->_messages[] = __('We can\'t retrieve a rate from %1 for %2.', $url, $currencyTo);
                    $data[$currencyFrom][$currencyTo] = null;
                } else {
                    $data[$currencyFrom][$currencyTo] = $this->_numberFormat(
                        (double)$response[$currencyFrom . $currencyTo]
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
            $jsonResponse = $httpClient->setUri(
                $url
            )->setConfig(
                [
                    'timeout' => $this->scopeConfig->getValue(
                        $this->timeoutConfigPath,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ),
                ]
            )->request(
                'GET'
            )->getBody();

            $jsonResponse = json_decode($jsonResponse, true);
            if (!empty($jsonResponse['query']['results']['rate'])) {
                $response = array_column($jsonResponse['query']['results']['rate'], 'Rate', 'id');
            }
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
    }

    /**
     * Build url for Currency Service
     *
     * @param string $currencyFrom
     * @param string[] $currenciesTo
     * @return string
     */
    private function buildUrl($currencyFrom, $currenciesTo)
    {
        $query = urlencode('select ') . '*' . urlencode(' from yahoo.finance.xchange where pair in (');
        $query .=
            urlencode(
                implode(
                    ',',
                    array_map(
                        function ($currencyTo) use ($currencyFrom) {
                            return '"' . $currencyFrom . $currencyTo . '"';
                        },
                        $currenciesTo
                    )
                )
            );
        $query .= ')';
        return str_replace('{{YQL_STRING}}', $query, $this->currencyConverterUrl);
    }
}

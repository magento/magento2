<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class CurrencyInformationAcquirerTest extends WebapiAbstract
{
    const SERVICE_NAME = 'directoryCurrencyInformationAcquirerV1';
    const RESOURCE_PATH = '/V1/directory/currency';
    const SERVICE_VERSION = 'V1';

    const STORE_CODE_FROM_FIXTURE = 'fixturestore';

    /**
     * @magentoApiDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGet()
    {
        /** @var $store \Magento\Store\Model\Group   */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
        $store->load(self::STORE_CODE_FROM_FIXTURE);
        $this->assertNotEmpty($store->getId(), 'Precondition failed: fixture store was not created.');

        $result = $this->getCurrencyInfo(self::STORE_CODE_FROM_FIXTURE);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('base_currency_code', $result);
        $this->assertArrayHasKey('base_currency_symbol', $result);
        $this->assertArrayHasKey('default_display_currency_code', $result);
        $this->assertArrayHasKey('default_display_currency_symbol', $result);
        $this->assertArrayHasKey('available_currency_codes', $result);
        $this->assertArrayHasKey('exchange_rates', $result);

        $this->assertTrue(
            in_array($result['base_currency_code'], $result['available_currency_codes']),
            'Missing the base currency code in the array of all available codes'
        );
        $this->assertTrue(
            in_array($result['default_display_currency_code'], $result['available_currency_codes']),
            'Missing the default display currency code in the array of all available codes'
        );

        // ensure the base currency is listed in the array of exchange rates, and has a rate of 1 (no conversion)
        $this->verifyExchangeRate($result['base_currency_code'], 1.0, $result['exchange_rates']);
    }

    /**
     * @param string $code
     * @param float $rate
     * @param array $exchangeRates
     */
    protected function verifyExchangeRate($code, $rate, $exchangeRates)
    {
        $this->assertNotEmpty($exchangeRates, 'Expected to have non-empty structure of exchange rates');

        $foundCode = false;
        $foundRate = false;
        foreach ($exchangeRates as $exchangeRate) {
            if ($code == $exchangeRate['currency_to']) {
                $foundCode = true;
                if ($rate == $exchangeRate['rate']) {
                    $foundRate = true;
                }
            }
        }

        $this->assertTrue($foundCode, 'Did not find currency code in the exchange rates: ' . $code);
        $this->assertTrue($foundRate, 'Did not find the expected rate for currency ' . $code . ': ' . $rate);
    }

    /**
     * Retrieve existing currency information for the store
     *
     * @param string $storeCode
     * @return \Magento\Directory\Api\Data\CurrencyInformationInterface
     */
    protected function getCurrencyInfo($storeCode = 'default')
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetCurrencyInfo',
            ],
        ];
        $requestData = ['storeId' => $storeCode];

        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Remove test store
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        /** @var \Magento\Framework\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var $store \Magento\Store\Model\Store */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
        $store->load(self::STORE_CODE_FROM_FIXTURE);
        if ($store->getId()) {
            $store->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }
}

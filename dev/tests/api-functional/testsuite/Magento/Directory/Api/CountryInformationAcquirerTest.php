<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

class CountryInformationAcquirerTest extends WebapiAbstract
{
    const SERVICE_NAME = 'directoryCountryInformationAcquirerV1';
    const RESOURCE_COUNTRIES_PATH = '/V1/directory/countries';
    const RESOURCE_COUNTRY = 'US';
    const SERVICE_VERSION = 'V1';

    const STORE_CODE_FROM_FIXTURE = 'fixturestore';

    /**
     * @magentoApiDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetCountries()
    {
        /** @var $store \Magento\Store\Model\Group   */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
        $store->load(self::STORE_CODE_FROM_FIXTURE);
        $this->assertNotEmpty($store->getId(), 'Precondition failed: fixture store was not created.');

        $result = $this->getCountriesInfo(self::STORE_CODE_FROM_FIXTURE);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('two_letter_abbreviation', $result[0]);
        $this->assertArrayHasKey('three_letter_abbreviation', $result[0]);
        $this->assertArrayHasKey('full_name_locale', $result[0]);
        $this->assertArrayHasKey('full_name_english', $result[0]);

        $this->assertSame('AD', $result[0]['id']);
        $this->assertSame('AD', $result[0]['two_letter_abbreviation']);
        $this->assertSame('AND', $result[0]['three_letter_abbreviation']);
        $this->assertSame('Andorra', $result[0]['full_name_english']);
    }

    /**
     * @magentoApiDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testGetCountry()
    {
        /** @var $store \Magento\Store\Model\Group   */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
        $store->load(self::STORE_CODE_FROM_FIXTURE);
        $this->assertNotEmpty($store->getId(), 'Precondition failed: fixture store was not created.');

        $result = $this->getCountryInfo(self::STORE_CODE_FROM_FIXTURE);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('two_letter_abbreviation', $result);
        $this->assertArrayHasKey('three_letter_abbreviation', $result);
        $this->assertArrayHasKey('full_name_locale', $result);
        $this->assertArrayHasKey('full_name_english', $result);
        $this->assertArrayHasKey('available_regions', $result);

        $this->assertSame('US', $result['id']);
        $this->assertSame('US', $result['two_letter_abbreviation']);
        $this->assertSame('USA', $result['three_letter_abbreviation']);
        $this->assertSame('United States', $result['full_name_english']);
    }

    /**
     * Retrieve existing country information for the store
     *
     * @param string $storeCode
     * @return \Magento\Directory\Api\Data\CountryInformationInterface
     */
    protected function getCountriesInfo($storeCode = 'default')
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_COUNTRIES_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetCountriesInfo',
            ],
        ];
        $requestData = ['storeId' => $storeCode];

        return $this->_webApiCall($serviceInfo, $requestData);
    }

    /**
     * Retrieve existing country information for the store
     *
     * @param string $storeCode
     * @return \Magento\Directory\Api\Data\CountryInformationInterface
     */
    protected function getCountryInfo($storeCode = 'default')
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_COUNTRIES_PATH . '/' . self::RESOURCE_COUNTRY,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetCountryInfo',
            ],
        ];
        $requestData = ['storeId' => $storeCode, 'countryId' => self::RESOURCE_COUNTRY];

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

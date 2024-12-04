<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi;

use Magento\Customer\Api\AccountManagementTest;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Customer as CustomerHelper;

class PartialResponseTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /** @var CustomerHelper */
    protected $customerHelper;

    /** @var string */
    protected $customerData;

    protected function setUp(): void
    {
        $this->_markTestAsRestOnly('Partial response functionality available in REST mode only.');

        $this->customerHelper = Bootstrap::getObjectManager()
            ->create(\Magento\TestFramework\Helper\Customer::class, ['name' => $this->name()]);

        $this->customerData = $this->customerHelper->createSampleCustomer();
    }

    public function testCustomerWithEmailFilter()
    {
        $filter = 'email';
        $expected = ['email' => $this->customerData['email']];
        $result = $this->_getCustomerWithFilter($filter, $this->customerData['id']);
        $this->assertEquals($expected, $result);
    }

    public function testCustomerWithEmailAndAddressFilter()
    {
        $filter = 'email,addresses[city]';
        $expected = [
            'email' => $this->customerData['email'],
            'addresses' => [
                ['city' => CustomerHelper::ADDRESS_CITY1],
                ['city' => CustomerHelper::ADDRESS_CITY2],
            ],
        ];
        $result = $this->_getCustomerWithFilter($filter, $this->customerData['id']);
        $this->assertEquals($expected, $result);
    }

    public function testCustomerWithNestedAddressFilter()
    {
        $filter = 'addresses[region[region_code]]';
        $expected = [
            'addresses' => [
                ['region' => ['region_code' => CustomerHelper::ADDRESS_REGION_CODE1]],
                ['region' => ['region_code' => CustomerHelper::ADDRESS_REGION_CODE2]],
            ],
        ];
        $result = $this->_getCustomerWithFilter($filter, $this->customerData['id']);
        $this->assertEquals($expected, $result);
    }

    public function testCustomerInvalidFilter()
    {
        // Invalid filter should return an empty result
        $result = $this->_getCustomerWithFilter('invalid', $this->customerData['id']);
        $this->assertEmpty($result);
    }

    public function testFilterForCustomerApiWithSimpleResponse()
    {
        $result = $this->_getCustomerWithFilter('customers', $this->customerData['id'], '/permissions/readonly');
        // assert if filter is ignored and a normal response is returned
        $this->assertFalse($result);
    }

    protected function _getCustomerWithFilter($filter, $customerId, $path = '')
    {
        $resourcePath = sprintf(
            '%s/%d%s?fields=%s',
            AccountManagementTest::RESOURCE_PATH,
            $customerId,
            $path,
            $filter
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => $resourcePath,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
        ];

        return $this->_webApiCall($serviceInfo);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Web API tests for CustomerGroupConfig model.
 */
class CustomerGroupConfigTest extends WebapiAbstract
{
    const SERVICE_NAME = "customerCustomerGroupConfigV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH = "/V1/customerGroups";

    /**
     * @return void
     */
    public function testSetDefaultGroup()
    {
        $customerGroupId = 1;
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/default/$customerGroupId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'customerCustomerGroupConfigV1SetDefaultCustomerGroup',
            ],
        ];
        $requestData = ['id' => $customerGroupId];
        $groupData = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals($customerGroupId, $groupData, "The default group does not match.");
    }

    /**
     * Web API test for setDefaultGroup() method when there is no customer group exists with provided ID.
     *
     * @return void
     */
    public function testSetDefaultGroupNonExistingGroup()
    {
        $customerGroupId = 10000;
        $expectedMessage = 'No such entity with %fieldName = %fieldValue';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/default/$customerGroupId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'customerCustomerGroupConfigV1SetDefaultCustomerGroup',
            ],
        ];
        $requestData = ['id' => $customerGroupId];
        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "Exception does not contain expected message."
            );
            $this->assertStringContainsString((string)$customerGroupId, $e->getMessage());
        }
    }
}

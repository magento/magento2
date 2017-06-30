<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Api;

use Magento\Customer\Model\Data\Group as CustomerGroup;
use Magento\Customer\Model\GroupRegistry;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Class GroupManagementTest
 */
class GroupManagementTest extends WebapiAbstract
{
    const SERVICE_NAME = "customerGroupManagementV1";
    const SERVICE_VERSION = "V1";
    const RESOURCE_PATH = "/V1/customerGroups";

    /**
     * @var GroupRegistry
     */
    private $groupRegistry;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->groupRegistry = $objectManager->get(\Magento\Customer\Model\GroupRegistry::class);
        $this->groupRepository = $objectManager->get(\Magento\Customer\Model\ResourceModel\GroupRepository::class);
    }

    /**
     * Verify the retrieval of the default group for storeId equal to 1.
     *
     * @param int $storeId The store Id
     * @param array $defaultGroupData The default group data for the store with the specified Id.
     *
     * @dataProvider getDefaultGroupDataProvider
     */
    public function testGetDefaultGroup($storeId, $defaultGroupData)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/default/$storeId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'customerGroupManagementV1GetDefaultGroup',
            ],
        ];
        $requestData = ['storeId' => $storeId];
        $groupData = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals($defaultGroupData, $groupData, "The default group does not match.");
    }

    /**
     * The testGetDefaultGroup data provider.
     *
     * @return array
     */
    public function getDefaultGroupDataProvider()
    {
        return [
            'admin' => [
                0,
                [
                    CustomerGroup::ID => 1,
                    CustomerGroup::CODE => 'General',
                    CustomerGroup::TAX_CLASS_ID => 3,
                    CustomerGroup::TAX_CLASS_NAME => 'Retail Customer'
                ],
            ],
            'base' => [
                1,
                [
                    CustomerGroup::ID => 1,
                    CustomerGroup::CODE => 'General',
                    CustomerGroup::TAX_CLASS_ID => 3,
                    CustomerGroup::TAX_CLASS_NAME => 'Retail Customer'
                ],
            ]
        ];
    }

    /**
     * Verify the retrieval of a non-existent storeId will return an expected fault.
     */
    public function testGetDefaultGroupNonExistentStore()
    {
        /* Store id should not exist */
        $nonExistentStoreId = 9876;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/default/$nonExistentStoreId",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'customerGroupManagementV1GetDefaultGroup',
            ],
        ];
        $requestData = ['storeId' => $nonExistentStoreId];
        $expectedMessage = 'No such entity with %fieldName = %fieldValue';

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "Exception does not contain expected message."
            );
            $this->assertContains((string)$nonExistentStoreId, $e->getMessage());
        }
    }

    /**
     * Verify that the group with the specified Id can or cannot be deleted.
     *
     * @param int $groupId The group Id
     * @param bool $isDeleteable Whether the group can or cannot be deleted.
     *
     * @dataProvider isReadonlyDataProvider
     */
    public function testIsReadonly($groupId, $isDeleteable)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$groupId/permissions",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'customerGroupManagementV1IsReadonly',
            ],
        ];

        $requestData = [CustomerGroup::ID => $groupId];

        $isReadonly = $this->_webApiCall($serviceInfo, $requestData);

        $failureMessage = $isDeleteable
            ? 'The group should be deleteable.' : 'The group should not be deleteable.';
        $this->assertEquals($isDeleteable, !$isReadonly, $failureMessage);
    }

    /**
     * The testIsReadonly data provider.
     *
     * @return array
     */
    public function isReadonlyDataProvider()
    {
        return [
            'NOT LOGGED IN' => [0, false],
            'General' => [1, false],
            'Wholesale' => [2, true],
            'Retailer' => [3, true]
        ];
    }

    /**
     * Verify that the group with the specified Id can or cannot be deleted.
     */
    public function testIsReadonlyNoSuchGroup()
    {
        /* This group ID should not exist in the store. */
        $groupId = 9999;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/$groupId/permissions",
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => 'customerGroupManagementV1IsReadonly',
            ],
        ];

        $requestData = [CustomerGroup::ID => $groupId];

        $expectedMessage = 'No such entity with %fieldName = %fieldValue';

        try {
            $this->_webApiCall($serviceInfo, $requestData);
            $this->fail("Expected exception.");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "Exception does not contain expected message."
            );
            $this->assertContains((string)$groupId, $e->getMessage());
        }
    }
}

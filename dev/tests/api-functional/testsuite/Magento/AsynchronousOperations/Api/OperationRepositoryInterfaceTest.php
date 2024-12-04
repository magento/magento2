<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class OperationRepositoryInterfaceTest extends WebapiAbstract
{
    public const RESOURCE_PATH = '/V1/bulk';
    public const SERVICE_NAME = 'asynchronousOperationsOperationRepositoryV1';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var MassSchedule
     */
    private $massSchedule;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->massSchedule = $this->objectManager->create(MassSchedule::class);

        parent::setUp();
    }

    /**
     * @magentoApiDataFixture Magento/AsynchronousOperations/_files/operation_searchable.php
     */
    public function testGetListByBulkStartTime()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'start_time',
                                'value' => '2010-10-10 00:00:00',
                                'condition_type' => 'lteq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 20,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertEquals(6, $response['total_count']);
        $this->assertCount(6, $response['items']);

        foreach ($response['items'] as $item) {
            $this->assertEquals('bulk-uuid-searchable-6', $item['bulk_uuid']);
        }
    }

    /**
     * @magentoApiDataFixture Magento/AsynchronousOperations/_files/operation_searchable.php
     */
    public function testGetList()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'bulk_uuid',
                                'value' => 'bulk-uuid-searchable-6',
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                    [
                        'filters' => [
                            [
                                'field' => 'status',
                                'value' => OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 20,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertEquals(1, $response['total_count']);
        $this->assertCount(1, $response['items']);

        foreach ($response['items'] as $item) {
            $this->assertEquals('bulk-uuid-searchable-6', $item['bulk_uuid']);
        }
    }

    /**
     * Check multiple bulk operation inserted followed by search via getList
     */
    public function testBulkGetListByStartTime()
    {
        $entityArray = [
            ['customer' => $this->getCustomer(), "password" => "Strong-Password"],
            [
                'customer' => $this->getCustomer('customer2@abc.com', 'Second'),
                "password" => "Strong-Password"
            ],
        ];

        // Adding bulk records twice
        $this->sendBulk($entityArray);
        $this->sendBulk($entityArray);

        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'start_time',
                                'value' => date('Y-m-d H:i:s', strtotime('-1 minute')),
                                'condition_type' => 'gt',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 20,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '?' . http_build_query($searchCriteria),
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertEquals(4, $response['total_count']);
        $this->assertCount(4, $response['items']);
    }

    public function sendBulk($customers)
    {
        $result = $this->massSchedule->publishMass(
            'async.magento.customer.api.accountmanagementinterface.createaccount.post',
            $customers
        );

        // Assert bulk accepted with no errors
        $this->assertFalse($result->isErrors());
    }

    private function getCustomer($email = 'customer1@abc.com', $firstName = 'First', $lastName = 'Customer')
    {
        /** @var $customer \Magento\Customer\Model\Data\Customer */
        $customer = $this->objectManager->create(CustomerInterface::class);
        $customer
            ->setFirstName($firstName)
            ->setLastname($lastName)
            ->setEmail($email);
        return $customer;
    }
}

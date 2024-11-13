<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales\CustomerOrders;

use Exception;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class OrdersTest
 */
class CustomerOrdersTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer.php
     */
    public function testOrdersQuery()
    {
        $query =
            <<<QUERY
            query {
                customer {
                    orders(filter: {}) { 
                        items { 
                            number  
                            status 
                            created_at
                        }
                    }
                }
            }
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $expectedData = [
            [
                'number' => '100000002',
                'status' => 'Processing',
                'created_at' => "2022-09-04 00:00:00"
            ],
            [
                'number' => '100000004',
                'status' => 'Closed',
                'created_at' => "2022-09-05 00:00:00"
            ],
            [
                'number' => '100000005',
                'status' => 'Complete',
                'created_at' => "2022-09-08 00:00:00"
            ],
            [
                'number' => '100000006',
                'status' => 'Complete',
                'created_at' => "2022-09-09 00:00:00"
            ]
        ];
  
        $actualData = $response['customer']['orders']['items'];
        foreach ($expectedData as $key => $data) {
            $this->assertEquals(
                $data['number'],
                $actualData[$key]['number'],
                "order_number is different than the expected for order - " . $data['number']
            );
        
            $this->assertEquals(
                $data['created_at'],
                $actualData[$key]['created_at'],
                "created_at is different than the expected for order - " . $data['created_at']
            );
        }
    }

    /**
     */
    public function testCustomerOrdersQueryNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
            {
                customerOrders {
                    items {
                    increment_id
                    grand_total
                    }
                }
            }
QUERY;
        $this->graphQlQuery($query);
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }
}

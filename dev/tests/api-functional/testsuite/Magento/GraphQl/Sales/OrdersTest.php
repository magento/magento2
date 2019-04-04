<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class OrdersTest
 */
class OrdersTest extends GraphQlAbstract
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
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
  customerOrders {
    items {
      id
      increment_id
      created_at
      grand_total
      status
    }
  }
}
QUERY;

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));

        $expectedData = [
            [
                'increment_id' => '100000002',
                'status' => 'processing',
                'grand_total' => 120.00
            ],
            [
                'increment_id' => '100000003',
                'status' => 'processing',
                'grand_total' => 130.00
            ],
            [
                'increment_id' => '100000004',
                'status' => 'closed',
                'grand_total' => 140.00
            ],
            [
                'increment_id' => '100000005',
                'status' => 'complete',
                'grand_total' => 150.00
            ],
            [
                'increment_id' => '100000006',
                'status' => 'complete',
                'grand_total' => 160.00
            ]
        ];

        $actualData = $response['customerOrders']['items'];

        foreach ($expectedData as $key => $data) {
            $this->assertEquals(
                $data['increment_id'],
                $actualData[$key]['increment_id'],
                "increment_id is different than the expected for order - " . $data['increment_id']
            );
            $this->assertEquals(
                $data['grand_total'],
                $actualData[$key]['grand_total'],
                "grand_total is different than the expected for order - " . $data['increment_id']
            );
            $this->assertEquals(
                $data['status'],
                $actualData[$key]['status'],
                "status is different than the expected for order - " . $data['increment_id']
            );
        }
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

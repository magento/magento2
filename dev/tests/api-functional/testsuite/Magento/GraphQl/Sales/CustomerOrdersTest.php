<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

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

    /**
     * @param $items
     * @param array $expectedData
     */
    public function extracted($items, array $expectedData): void
    {
        $actualData = $items;

        foreach ($expectedData as $key => $data) {
            $this->assertEquals(
                $data['order_number'],
                $actualData[$key]['number'],
                "order_number is different than the expected for order - " . $data['order_number']
            );
            $this->assertEquals(
                $data['status'],
                $actualData[$key]['status'],
                "status is different than the expected for order - " . $data['order_number']
            );
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);
    }

    /**
     * @magentoConfigFixture default_store customer/account_share/scope 0
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer_different_stores.php
     */
    public function testOrderListForGlobalCustomerViewQuery()
    {
        $query =
            <<<QUERY
query {
  customer {
    orders {
      items {
        number,
        status
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
                'order_number' => '100000004',
                'status' => 'Processing'
            ],
            [
                'order_number' => '200000001',
                'status' => 'Pending'
            ]
        ];

        $this->extracted($response['customer']['orders']['items'], $expectedData);
    }

    /**
     * @magentoConfigFixture default_store customer/account_share/scope 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/Sales/_files/orders_with_customer_different_stores.php
     */
    public function testOrderListForWebsiteCustomerViewQuery()
    {
        $query =
            <<<QUERY
query {
  customer {
    orders {
      items {
        number,
        status
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
                'order_number' => '100000004',
                'status' => 'Processing'
            ],
            [
                'order_number' => '200000001',
                'status' => 'Pending'
            ]
        ];

        $this->extracted($response['customer']['orders']['items'], $expectedData);
    }

    /**
     */
    public function testOrdersQueryNotAuthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = <<<QUERY
{
  customer {
    orders {
      items {
        number
      }
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

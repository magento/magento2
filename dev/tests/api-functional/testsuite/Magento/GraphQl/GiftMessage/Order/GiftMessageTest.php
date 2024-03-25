<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftMessage\Order;

use Exception;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class GiftMessageTest extends GraphQlAbstract
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
     * @magentoConfigFixture default_store sales/gift_options/allow_order 1
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GiftMessage/_files/customer/order_with_message.php
     * @throws AuthenticationException
     * @throws Exception
     */
    public function testGiftMessageForOrder()
    {
        $query = $this->getCustomerOrdersQuery();
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
        foreach ($response['customerOrders']['items'] as $order) {
            self::assertArrayHasKey('gift_message', $order);
            self::assertArrayHasKey('to', $order['gift_message']);
            self::assertArrayHasKey('from', $order['gift_message']);
            self::assertArrayHasKey('message', $order['gift_message']);
        }
    }

    /**
     * @magentoConfigFixture default_store sales/gift_options/allow_order 1
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GiftMessage/_files/customer/order_with_message.php
     * @throws AuthenticationException
     * @throws Exception
     */
    public function testGiftMessageForCustomerOrder()
    {
        $query = <<<QUERY
query {
  customer {
   orders(filter:{number:{eq:"999999991"}}){
    total_count
    items
    {
      gift_message {
        from
        to
        message
      }
     }
   }
 }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
        foreach ($response['customer']['orders']['items'] as $order) {
            self::assertArrayHasKey('gift_message', $order);
            self::assertSame('Jane Roe', $order['gift_message']['to']);
            self::assertSame('John Doe', $order['gift_message']['from']);
            self::assertSame('Gift Message Text', $order['gift_message']['message']);
        }
    }

    /**
     * @magentoConfigFixture default_store sales/gift_options/allow_order 0
     * @magentoConfigFixture default_store sales/gift_options/allow_items 0
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     * @magentoApiDataFixture Magento/GiftMessage/_files/customer/order_with_message.php
     */
    public function testGiftMessageNotAllowForOrder()
    {
        $query = $this->getCustomerOrdersQuery();
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders($currentEmail, $currentPassword)
        );
        self::assertEquals(1, count($response['customerOrders']));
        foreach ($response['customerOrders']['items'] as $order) {
            self::assertArrayHasKey('gift_message', $order);
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return array
     * @throws AuthenticationException
     */
    private function getCustomerAuthHeaders(string $email, string $password): array
    {
        $customerToken = $this->customerTokenService->createCustomerAccessToken($email, $password);
        return ['Authorization' => 'Bearer ' . $customerToken];
    }

    /**
     * Get Customer Orders query
     *
     * @return string
     */
    private function getCustomerOrdersQuery()
    {
        return <<<QUERY
query {
    customerOrders {
        items {
            order_number
            gift_message {
                to
                from
                message
            }
        }
    }
}
QUERY;
    }
}

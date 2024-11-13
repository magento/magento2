<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\GiftMessage\Order\Item;

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
    public function testGiftMessageForOrderItem()
    {
        $query = <<<QUERY
query {
  customer {
   orders(filter:{number:{eq:"999999991"}}){
    total_count
    items {
       id
       items{
         gift_message {
            from
            to
            message
         }
       }
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
        foreach ($response['customer']['orders']['items'][0]['items'] as $item) {
            self::assertArrayHasKey('gift_message', $item);
            self::assertSame('Luci', $item['gift_message']['to']);
            self::assertSame('Jack', $item['gift_message']['from']);
            self::assertSame('Good Job!', $item['gift_message']['message']);
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
}

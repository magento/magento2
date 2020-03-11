<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test Reorder
 */
class ReorderTest extends GraphQlAbstract
{
    private const CUSTOMER_ID = 1;
    private const ORDER_NUMBER = '100000001';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    protected function setUp()
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);

        // be sure previous tests didn't left customer quote
        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = Bootstrap::getObjectManager()->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        try {
            $quote = $cartRepository->getForCustomer(self::CUSTOMER_ID);
            $cartRepository->delete($quote);
        } catch (NoSuchEntityException $e) {
        }
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_order_item_with_product_and_custom_options.php
     */
    public function testReorderMutation()
    {
        $query = $this->getQuery(self::ORDER_NUMBER);

        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';

        $response = $this->graphQlMutation($query, [], '', $this->getCustomerAuthHeaders($currentEmail, $currentPassword));
        $this->assertResponseFields(
            $response['addAllOrderItemsToCart'] ?? [],
            [
                'cart' => [
                    'email' => $currentEmail,
                    'total_quantity' => 1,
                    'items' => [
                        [
                            'quantity' => 1,
                            'product' => [
                                'sku' => 'simple',
                            ],
                        ]
                    ],
                ],
                'errors' => []
            ]
        );

    }


    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_order_item_with_product_and_custom_options.php
     * @expectedException \Exception
     * @expectedExceptionMessage The current customer isn't authorized.
     */
    public function testReorderWithoutAuthorisedCustomer()
    {
        $query = $this->getQuery(self::ORDER_NUMBER);
        $this->graphQlMutation($query);
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

    /**
     * @param string $orderNumber
     * @return string
     */
    protected function getQuery($orderNumber): string
    {
        $query =
            <<<MUTATION
mutation {
  addAllOrderItemsToCart(orderNumber: "{$orderNumber}") {
    errors {
      sku
      message
    }
    cart {
        email
        total_quantity
        items {
            quantity
            product {
                sku
            }
        }
      }
    }
}
MUTATION;
        return $query;
    }
}

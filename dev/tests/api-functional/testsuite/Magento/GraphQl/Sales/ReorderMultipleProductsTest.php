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
 * Test Reorder with different types of product.
 */
class ReorderMultipleProductsTest extends GraphQlAbstract
{
    /**
     * Customer Id
     */
    private const CUSTOMER_ID = 1;

    /**
     * Order Number
     */
    private const ORDER_NUMBER = '100000001';

    /**
     * Customer email
     */
    private const CUSTOMER_EMAIL = 'customer@example.com';

    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);

        // be sure previous tests didn't left customer quote
        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        try {
            $quote = $cartRepository->getForCustomer(self::CUSTOMER_ID);
            $cartRepository->delete($quote);
        } catch (NoSuchEntityException $e) {
        }
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_with_different_types_of_product.php
     */
    public function testMutplipleProducts()
    {
        $response = $this->makeReorderForDefaultCustomer(self::ORDER_NUMBER);

        $expectedResponse = [
            'userInputErrors' => [],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 5,
                'items' => [
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'simple-2',
                        ],
                    ],
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'configurable-1',
                        ],
                        'configurable_options' => [
                            [
                                'option_label' => 'Test Configurable',
                                'value_label' => 'Option 1',
                            ],
                        ],
                    ],
                    [
                        'quantity' => 1,
                        'product' =>
                            [
                                'sku' => 'virtual_product',
                            ],
                    ],
                    [
                        'quantity' => 1,
                        'product' => [
                                'sku' => 'bundle-product-radio-required-option',
                            ],
                        'bundle_options' => [
                            [
                                'label' => 'Radio Options',
                                'type' => 'radio',
                                'values' => [
                                    [
                                        'label' => 'Simple Product',
                                        'price' => 10,
                                        'quantity' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'downloadable-product',
                        ],
                        'links' => [
                            [
                                'title' => 'Downloadable Product Link',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertResponseFields($response['reorderItems'] ?? [], $expectedResponse);
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
     * Execute GraphQL Mutation for default customer (make reorder)
     *
     * @param string $orderId
     * @return array|bool|float|int|string
     * @throws \Exception
     */
    private function makeReorderForDefaultCustomer(string $orderId = self::ORDER_NUMBER)
    {
        $query = $this->getQuery($orderId);
        $currentPassword = 'password';
        return $this->graphQlMutation(
            $query,
            [],
            '',
            $this->getCustomerAuthHeaders(self::CUSTOMER_EMAIL, $currentPassword)
        );
    }

    /**
     * @param string $orderNumber
     * @return string
     */
    protected function getQuery($orderNumber): string
    {
        return
            <<<MUTATION
mutation {
  reorderItems(orderNumber: "{$orderNumber}") {
    userInputErrors {
      path
      code
    }
    cart {
      email
      total_quantity
      items {
        quantity
        product {
          sku
        }
        ... on ConfigurableCartItem {
          configurable_options {
            option_label
            value_label
          }
        }
        ... on BundleCartItem {
          bundle_options {
            label
            type
            values {
              label
              price
              quantity
            }
          }
        }
        ... on DownloadableCartItem {
            links {
                title
            }
        }
      }
    }
  }
}
MUTATION;
    }
}

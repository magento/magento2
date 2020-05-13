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
 * Test Reorder with and without products overlay in shopping cart.
 */
class ReorderOverlayProductTest extends GraphQlAbstract
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
     * @magentoApiDataFixture Magento/Sales/_files/customer_order_item_with_product_and_custom_options.php
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple_without_custom_options.php
     * @throws NoSuchEntityException
     */
    public function testWithoutOverlay()
    {
        $response = $this->addProductToCartAndReorder();

        $expectedResponse = [
            'userInputErrors' => [],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 2,
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
                            'sku' => 'simple',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertResponseFields($response['reorderItems'] ?? [], $expectedResponse);
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_with_two_simple_products.php
     * @throws NoSuchEntityException
     */
    public function testWithOverlay()
    {
        $response = $this->addProductToCartAndReorder();

        $expectedResponse = [
            'userInputErrors' => [],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 3,
                'items' => [
                    [
                        'quantity' => 2,
                        'product' => [
                            'sku' => 'simple-2',
                        ],
                    ],
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'simple',
                        ],
                    ],
                ],
            ],
        ];
        $this->assertResponseFields($response['reorderItems'] ?? [], $expectedResponse);
    }

    /**
     * @return array|bool|float|int|string
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    private function addProductToCartAndReorder()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('simple-2');
        /** @var CartRepositoryInterface $cartRepository */
        $cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);

        /** @var \Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer $emptyCartForCustomer */
        $emptyCartForCustomer = Bootstrap::getObjectManager()
            ->create(\Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer::class);
        $emptyCartForCustomer->execute(self::CUSTOMER_ID);
        $quote = $cartRepository->getForCustomer(self::CUSTOMER_ID);
        $quote->addProduct($product, 1);
        $cartRepository->save($quote);

        return $this->makeReorderForDefaultCustomer(self::ORDER_NUMBER);
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
        }
      }
    }
}
MUTATION;
    }
}

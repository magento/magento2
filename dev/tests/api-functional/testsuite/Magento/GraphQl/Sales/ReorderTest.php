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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ReorderTest extends GraphQlAbstract
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
        $response = $this->makeReorderForDefaultCustomer();
        $this->assertResponseFields(
            $response['reorderItems'] ?? [],
            [
                'cart' => [
                    'email' => self::CUSTOMER_EMAIL,
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
                'userInputErrors' => []
            ]
        );
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/customer_order_item_with_product_and_custom_options.php
     */
    public function testReorderWithoutAuthorisedCustomer()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $query = $this->getQuery(self::ORDER_NUMBER);
        $this->graphQlMutation($query);
    }

    /**
     * Test reorder when simple product is out of stock/disabled/deleted
     *
     * @magentoApiDataFixture Magento/Sales/_files/order_with_product_out_of_stock.php
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function testSimpleProductOutOfStock()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $repository */
        $productRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productSku = 'simple-2';
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get($productSku);

        $this->assertProductNotAvailable();
        $this->assertDisabledProduct($productRepository, $product);
        $this->assertWithDeletedProduct($productRepository, $product);
    }

    /**
     * Test reorder when simple product qty is 0, with allowed backorders configured to below 0
     *
     * @magentoApiDataFixture Magento/Sales/_files/order_with_zero_qty_product.php
     * @throws \Exception
     */
    public function testBackorderWithOutOfStock()
    {
        $response = $this->makeReorderForDefaultCustomer();
        $expectedResponse = [
            'userInputErrors' => [],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 2,
                'items' => [
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'simple'
                        ]
                    ],
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'simple-2'
                        ]
                    ]
                ],
            ],
        ];
        $this->assertResponseFields($response['reorderItems'], $expectedResponse);
    }

    /**
     * Test reorder with low stock for simple product
     *
     * @magentoApiDataFixture Magento/Sales/_files/order_with_1_qty_product.php
     * @throws \Exception
     */
    public function testReorderWithLowStock()
    {
        $response = $this->makeReorderForDefaultCustomer();
        $expectedResponse = [
            'userInputErrors' => [
                [
                    'path' => ['orderNumber'],
                    'code' => 'INSUFFICIENT_STOCK',
                ],
            ],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 10,
                'items' => [
                    [
                        'quantity' => 10,
                        'product' => [
                            'sku' => 'simple-2'
                        ]
                    ]
                ],
            ],
        ];
        $this->assertResponseFields($response['reorderItems'], $expectedResponse);
        $response = $this->makeReorderForDefaultCustomer();
        $expectedResponse['cart']['total_quantity'] = 20;
        $expectedResponse['cart']['items'][0]['quantity'] = 20;

        $this->assertResponseFields($response['reorderItems'], $expectedResponse);
    }

    /**
     * Assert that simple product is not available.
     */
    private function assertProductNotAvailable()
    {
        $response = $this->makeReorderForDefaultCustomer();
        $expectedResponse = [
            'userInputErrors' => [
                [
                    'path' => ['orderNumber'],
                    'code' => 'NOT_SALABLE',
                ],
            ],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 0,
                'items' => [],
            ],
        ];
        $this->assertResponseFields($response['reorderItems'] ?? [], $expectedResponse);
    }

    /**
     * Assert reorder with disabled product.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    private function assertDisabledProduct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ): void {
        // make product available in stock but disable and make reorder
        $product->setStockData(
            [
                'use_config_manage_stock'   => 1,
                'qty'                       => 100,
                'is_qty_decimal'            => 0,
                'is_in_stock'               => 1,
            ]
        )
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $productRepository->save($product);
        $this->assertProductNotAvailable();
    }

    /**
     * Assert reorder with deleted product.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @throws \Magento\Framework\Exception\StateException
     * @return void
     */
    private function assertWithDeletedProduct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ): void {
        // delete a product and make reorder
        /** @var \Magento\Framework\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $productRepository->delete($product);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);

        $expectedResponse = [
            'userInputErrors' => [
                [
                    'path' => ['orderNumber'],
                    'code' => 'PRODUCT_NOT_FOUND',
                ],
            ],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 0,
                'items' => [],
            ],
        ];
        $response = $this->makeReorderForDefaultCustomer();
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
        $query =
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
        return $query;
    }
}

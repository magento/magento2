<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Sales;

use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * Test Reorder with and without products overlay in shopping cart.
 */
class ReorderConfigurableWithVariationsTest extends GraphQlAbstract
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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->customerTokenService = Bootstrap::getObjectManager()->get(CustomerTokenServiceInterface::class);

        // be sure previous tests didn't left customer quote
        /** @var CartRepositoryInterface $cartRepository */
        $this->cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        try {
            $quote = $this->cartRepository->getForCustomer(self::CUSTOMER_ID);
            $this->cartRepository->delete($quote);
        } catch (NoSuchEntityException $e) {
        }
    }

    /**
     * @magentoApiDataFixture Magento/Sales/_files/order_with_two_configurable_variations.php
     */
    public function testVariations()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $repository */
        $productRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productSku = 'simple_20';
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get($productSku);
        $this->assertValidVariations();
        $this->assertWithOutOfStockVariation($productRepository, $product);
    }

    /**
     * Assert 2 variations of configurable product.
     *
     * @return void
     * @throws NoSuchEntityException
     */
    private function assertValidVariations(): void
    {
        $response = $this->makeReorderForDefaultCustomer(self::ORDER_NUMBER);

        $expectedResponse = [
            'userInputErrors' => [],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 2,
                'items' => [
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'configurable',
                        ],
                        'configurable_options' => [
                            [
                                'option_label' => 'Test Configurable',
                                'value_label' => 'Option 1',
                            ]
                        ],
                    ],
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'configurable',
                        ],
                        'configurable_options' => [
                            [
                                'option_label' => 'Test Configurable',
                                'value_label' => 'Option 2',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertResponseFields($response['reorderItems'] ?? [], $expectedResponse);
        $this->cartRepository->delete($this->cartRepository->getForCustomer(self::CUSTOMER_ID));
    }

    /**
     * Assert reorder with out of stock variation.
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws NoSuchEntityException
     */
    private function assertWithOutOfStockVariation(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ): void {
        /** @var $stockRegistryStorage StockRegistryStorage */
        $stockRegistryStorage = Bootstrap::getObjectManager()->get(StockRegistryStorage::class);
        // clean stock registry
        $stockRegistryStorage->clean();
        // make product available in stock but disable and make reorder
        $product->setStockData(
            [
                'use_config_manage_stock'   => 1,
                'qty'                       => 0,
                'is_qty_decimal'            => 0,
                'is_in_stock'               => 0,
            ]
        );
        $productRepository->save($product);
        $response = $this->makeReorderForDefaultCustomer(self::ORDER_NUMBER);
        $this->assetProductNotSalable($response);
        $this->cartRepository->delete($this->cartRepository->getForCustomer(self::CUSTOMER_ID));
    }

    /**
     * Assert reorder with "out of stock" variation.
     *
     * @magentoApiDataFixture Magento/Sales/_files/order_with_two_configurable_variations.php
     * @return void
     * @throws \Magento\Framework\Exception\StateException
     * @throws NoSuchEntityException
     */
    public function testWithDeletedVariation(): void
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $repository */
        $productRepository = Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $productSku = 'simple_20';
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $productRepository->get($productSku);
        // delete a product and make reorder
        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        $productRepository->delete($product);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);

        $response = $this->makeReorderForDefaultCustomer(self::ORDER_NUMBER);
        $this->assetProductUndefined($response);
        $this->cartRepository->delete($this->cartRepository->getForCustomer(self::CUSTOMER_ID));
    }

    /**
     * Assert that variation is not salable.
     *
     * @param array $response
     * @return void
     */
    private function assetProductNotSalable(array $response)
    {
        $expectedResponse = [
            'userInputErrors' => [
                [
                    'path' => [
                        'orderNumber',
                    ],
                    'code' => 'NOT_SALABLE',
                ],
            ],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 1,
                'items' => [
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'configurable',
                        ],
                        'configurable_options' => [
                            [
                                'option_label' => 'Test Configurable',
                                'value_label' => 'Option 1',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertResponseFields($response['reorderItems'] ?? [], $expectedResponse);
    }

    /**
     * Assert condition that variation is undefined.
     *
     * @param array $response
     * @return void
     */
    private function assetProductUndefined(array $response): void
    {
        $expectedResponse = [
            'userInputErrors' => [
                [
                    'path' => [
                        'orderNumber',
                    ],
                    'code' => 'UNDEFINED',
                ],
            ],
            'cart' => [
                'email' => 'customer@example.com',
                'total_quantity' => 1,
                'items' => [
                    [
                        'quantity' => 1,
                        'product' => [
                            'sku' => 'configurable',
                        ],
                        'configurable_options' => [
                            [
                                'option_label' => 'Test Configurable',
                                'value_label' => 'Option 1',
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
      }
    }
  }
}
MUTATION;
    }
}

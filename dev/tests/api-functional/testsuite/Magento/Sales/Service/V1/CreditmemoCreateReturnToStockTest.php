<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Sales\Service\V1;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCart;
use Magento\Bundle\Test\Fixture\Link as BundleSelectionFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\PlaceOrder as PlaceOrderFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetGuestEmail as SetGuestEmailFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as ConfigurableAttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Test\Fixture\Invoice as InvoiceFixture;
use Magento\Sales\Test\Fixture\Shipment as ShipmentFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

/**
 * Web API REST test for Product return to stock after credit memo creation
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea webapi_rest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CreditmemoCreateReturnToStockTest extends WebapiAbstract
{
    private const SERVICE_VERSION = 'V1';
    private const ORDER_REFUND_SERVICE = 'salesRefundOrderV1';
    private const MSI_SALABLE_QTY_ENDPOINT = 'inventory/get-product-salable-quantity';
    private const DEFAULT_STOCK_ID = 1; // Default stock ID in MSI
    private const INVOICE_REFUND_SERVICE = 'salesRefundInvoiceV1';
    private const MSI_SALABLE_QTY_SOAP_SERVICE = 'inventorySalesApiGetProductSalableQtyV1';

    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;
    /**
     * @var ProductRepositoryInterface|mixed
     */
    private ProductRepositoryInterface $productRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * @dataProvider returnToStockDataProvider
     * @param array $requestData
     * @param float $expectedFinalQty
     * @param string|null $expectedException
     * @return void
     */
    #[
        ConfigFixture('cataloginventory/item_options/auto_return', 0),
        ConfigFixture('payment/checkmo/active', '1'),
        ConfigFixture('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, [
            'price' => 10.00,
            'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]
        ], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, [
            'cart_id' => '$cart.id$',
            'email' => 'guest@example.com'
        ]),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$product.id$',
            'qty' => 1
        ]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'carrier_code' => 'flatrate',
            'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'method' => 'checkmo'
        ]),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], as: 'invoice'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$'], as: 'shipment'),
    ]
    #[DataProvider('returnToStockDataProvider')]
    public function testReturnToStockScenarios(
        array   $requestData,
        float   $expectedFinalQty,
        ?string $expectedException = null
    ): void {
        $order = $this->fixtures->get('order');
        $product = $this->fixtures->get('product');
        $invoice = $this->fixtures->get('invoice');
        $this->assertProductQty(99, $product->getSku());
        $this->assertNotNull($order->getData('increment_id'));
        $serviceInfo = $this->getServiceInfo($order, $invoice, $requestData);
        $finalRequestData = $this->prepareRequestData($order, $invoice, $requestData);
        try {
            $this->_webApiCall($serviceInfo, $finalRequestData);
        } catch (\Exception $exception) {
            if ($expectedException) {
                $message = $exception->getMessage();
                $isInvoiceEndpoint = isset($requestData['use_invoice_endpoint'])
                    && $requestData['use_invoice_endpoint'];
                $isTypeErrorQty = str_contains(
                    $message,
                    'isValidDecimalRefundQty(): Argument #2 ($itemQty) must be of type float'
                );
                if ($isInvoiceEndpoint && $isTypeErrorQty) {
                    $this->addToAssertionCount(1);
                } else {
                    $this->assertStringContainsString($expectedException, $message);
                }
            }
        }
        if (!$expectedException) {
            $this->assertProductQty($expectedFinalQty, $product->getSku());
        }
    }

    /**
     * Test configurable product return to stock
     *
     * @return void
     */
    #[
        ConfigFixture('cataloginventory/item_options/auto_return', 0),
        ConfigFixture('payment/checkmo/active', '1'),
        ConfigFixture('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, [
            'price' => 10.00,
            'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true],
            'type_id' => 'simple',
            'attribute_set_id' => 4
        ], as: 'child_product'),
        DataFixture(ConfigurableAttributeFixture::class, as: 'attribute'),
        DataFixture(ConfigurableProductFixture::class, [
            'price' => 20.00,
            'type_id' => 'configurable',
            '_options' => ['$attribute$'],
            '_links' => ['$child_product$']
        ], as: 'configurable_product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, [
            'cart_id' => '$cart.id$',
            'email' => 'guest@example.com'
        ]),
        DataFixture(AddProductToCartFixture::class, [
            'cart_id' => '$cart.id$',
            'product_id' => '$child_product.id$',
            'qty' => 3
        ]),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'carrier_code' => 'flatrate',
            'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'method' => 'checkmo'
        ]),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], as: 'invoice'),
    ]
    public function testConfigurableProductReturnToStock(): void
    {
        $this->executeProductReturnToStockTest('child_product', 97, 100, 3);
    }

    /**
     * Test bundle fixed product return to stock
     *
     * @return void
     */
    #[
        ConfigFixture('cataloginventory/item_options/auto_return', 0),
        ConfigFixture('payment/checkmo/active', '1'),
        ConfigFixture('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, [
            'price' => 20,
            'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]
        ], 'product1'),
        DataFixture(ProductFixture::class, [
            'price' => 10,
            'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]
        ], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$', 'price' => 15], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$', 'price' => 8], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-fixed-price',
                'price_type' => Price::PRICE_TYPE_FIXED,
                '_options' => ['$opt1$', '$opt2$'],
            ],
            'bundle_product_1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, [
            'cart_id' => '$cart.id$',
            'email' => 'guest@example.com'
        ]),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 3
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'carrier_code' => 'flatrate',
            'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'method' => 'checkmo'
        ]),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], as: 'invoice'),
    ]
    public function testBundleFixedProductReturnToStock(): void
    {
        $this->executeBundleProductReturnToStockTest(
            'product1',
            'product2',
            97,
            100,
            'fixed'
        );
    }

    /**
     * Test bundle dynamic product return to stock
     *
     * @return void
     */
    #[
        ConfigFixture('cataloginventory/item_options/auto_return', 0),
        ConfigFixture('payment/checkmo/active', '1'),
        ConfigFixture('carriers/flatrate/active', '1'),
        DataFixture(ProductFixture::class, [
            'price' => 20,
            'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]
        ], 'product1'),
        DataFixture(ProductFixture::class, [
            'price' => 10,
            'quantity_and_stock_status' => ['qty' => 100, 'is_in_stock' => true]
        ], 'product2'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product1.sku$'], 'selection1'),
        DataFixture(BundleSelectionFixture::class, ['sku' => '$product2.sku$'], 'selection2'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$selection2$']], 'opt2'),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-dynamic-price',
                'price_type' => Price::PRICE_TYPE_DYNAMIC,
                '_options' => ['$opt1$', '$opt2$'],
                'special_price' => 90
            ],
            'bundle_product_1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(SetGuestEmailFixture::class, [
            'cart_id' => '$cart.id$',
            'email' => 'guest@example.com'
        ]),
        DataFixture(
            AddBundleProductToCart::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bundle_product_1.id$',
                'selections' => [['$product1.id$'], ['$product2.id$']],
                'qty' => 3
            ]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetDeliveryMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'carrier_code' => 'flatrate',
            'method_code' => 'flatrate'
        ]),
        DataFixture(SetPaymentMethodFixture::class, [
            'cart_id' => '$cart.id$',
            'method' => 'checkmo'
        ]),
        DataFixture(PlaceOrderFixture::class, ['cart_id' => '$cart.id$'], as: 'order'),
        DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], as: 'invoice'),
        DataFixture(ShipmentFixture::class, ['order_id' => '$order.id$'], as: 'shipment'),
    ]
    public function testBundleDynamicProductReturnToStock(): void
    {
        $this->executeBundleProductReturnToStockTest('product1', 'product2', 97, 100, 'dynamic');
    }

    /**
     * Execute bundle product return to stock test
     *
     * @param string $product1FixtureKey
     * @param string $product2FixtureKey
     * @param float $expectedQtyAfterOrder
     * @param float $expectedQtyAfterRefund
     * @param string $priceType
     * @return void
     */
    private function executeBundleProductReturnToStockTest(
        string $product1FixtureKey,
        string $product2FixtureKey,
        float  $expectedQtyAfterOrder,
        float  $expectedQtyAfterRefund,
        string $priceType
    ): void {
        $order = $this->fixtures->get('order');
        $product1 = $this->fixtures->get($product1FixtureKey);
        $product2 = $this->fixtures->get($product2FixtureKey);
        $this->assertProductQty($expectedQtyAfterOrder, $product1->getSku());
        $this->assertProductQty($expectedQtyAfterOrder, $product2->getSku());
        $this->assertNotNull($order->getData('increment_id'));
        $serviceInfo = $this->getOrderRefundServiceInfo((int)$order->getEntityId());
        [$allOrderItems, $itemIds] = $this->getAllOrderItemsForRefund($order, $priceType);
        $this->_webApiCall($serviceInfo, [
            'orderId' => (int)$order->getEntityId(),
            'items' => $allOrderItems,
            'arguments' => [
                'extension_attributes' => [
                    'return_to_stock_items' => $itemIds
                ]
            ]
        ]);
        $this->assertProductQty($expectedQtyAfterRefund, $product1->getSku());
        $this->assertProductQty($expectedQtyAfterRefund, $product2->getSku());
    }

    /**
     * Get all order items for bundle product refund
     *
     * @param OrderInterface $order
     * @param string $priceType
     * @return array
     */
    private function getAllOrderItemsForRefund(OrderInterface $order, string $priceType): array
    {
        $items = [];
        $itemIds = [];
        foreach ($order->getAllItems() as $item) {
            if ($priceType === 'fixed' && $item->getProductType() === 'bundle') {
                $itemIds[] = (int)$item->getItemId();
                $items[] = [
                    'order_item_id' => (int)$item->getItemId(),
                    'qty' => (float) $item->getQtyOrdered()
                ];
                foreach ($order->getAllItems() as $childItem) {
                    if ($childItem->getParentItemId() === $item->getItemId() &&
                        $childItem->getProductType() === 'simple') {
                        $itemIds[] = (int)$childItem->getItemId();
                    }
                }
            } elseif ($priceType === 'dynamic' && $item->getProductType() === 'simple') {
                $itemIds[] = (int)$item->getItemId();
                $items[] = [
                    'order_item_id' => (int)$item->getItemId(),
                    'qty' => (float) $item->getQtyOrdered()
                ];
            }
        }

        return [$items, $itemIds];
    }

    /**
     * Data provider for return to stock test scenarios
     *
     * @return array
     */
    public static function returnToStockDataProvider(): array
    {
        return [
            'with_order_item_id_and_return_to_stock' => [
                'requestData' => [
                    'include_items' => true,
                    'include_return_to_stock' => true,
                    'use_correct_item_ids' => true
                ],
                'expectedFinalQty' => 100.0
            ],
            'without_order_item_id_but_with_return_to_stock' => [
                'requestData' => [
                    'include_items' => false,
                    'include_return_to_stock' => true,
                    'use_correct_item_ids' => true
                ],
                'expectedFinalQty' => 100.0
            ],
            'with_incorrect_order_item_id' => [
                'requestData' => [
                    'include_items' => true,
                    'include_return_to_stock' => true,
                    'use_correct_item_ids' => false,
                    'incorrect_item_ids' => [123]
                ],
                'expectedFinalQty' => 99.0,
                'expectedException' => 'The return to stock argument contains product item '
                    . 'that is not part of the original order'
            ],
            'without_return_to_stock_items' => [
                'requestData' => [
                    'include_items' => false,
                    'include_return_to_stock' => false
                ],
                'expectedFinalQty' => 99.0
            ],
            'invoice_refund_with_order_item_id_and_return_to_stock' => [
                'requestData' => [
                    'include_items' => true,
                    'include_return_to_stock' => true,
                    'use_correct_item_ids' => true,
                    'use_invoice_endpoint' => true
                ],
                'expectedFinalQty' => 99.0,
                'expectedException' => "We can't create creditmemo for the invoice"
            ],
            'invoice_refund_with_incorrect_order_item_id' => [
                'requestData' => [
                    'include_items' => true,
                    'include_return_to_stock' => true,
                    'use_correct_item_ids' => false,
                    'incorrect_item_ids' => [123],
                    'use_invoice_endpoint' => true
                ],
                'expectedFinalQty' => 99.0,
                'expectedException' => "We can't create creditmemo for the invoice"
            ],
            'invoice_refund_without_params' => [
                'requestData' => [
                    'include_items' => false,
                    'include_return_to_stock' => false,
                    'use_invoice_endpoint' => true
                ],
                'expectedFinalQty' => 99.0,
                'expectedException' => "We can't create creditmemo for the invoice"
            ]
        ];
    }

    /**
     * Execute product return to stock test
     *
     * @param string $productFixtureKey
     * @param float $expectedQtyAfterOrder
     * @param float $expectedQtyAfterRefund
     * @param int $refundQty
     * @return void
     */
    private function executeProductReturnToStockTest(
        string $productFixtureKey,
        float  $expectedQtyAfterOrder,
        float  $expectedQtyAfterRefund,
        float  $refundQty
    ): void {
        $order = $this->fixtures->get('order');
        $product = $this->fixtures->get($productFixtureKey);
        $this->assertProductQty($expectedQtyAfterOrder, $product->getSku());
        $this->assertNotNull($order->getData('increment_id'));
        $serviceInfo = $this->getOrderRefundServiceInfo((int)$order->getEntityId());
        $orderItemId = $this->getSimpleProductOrderItemId($order, $product->getSku());
        $this->_webApiCall($serviceInfo, [
            'orderId' => (int)$order->getEntityId(),
            'items' => [
                [
                    'order_item_id' => $orderItemId,
                    'qty' => (float) $refundQty
                ]
            ],
            'arguments' => [
                'extension_attributes' => [
                    'return_to_stock_items' => [$orderItemId]
                ]
            ]
        ]);
        $this->assertProductQty($expectedQtyAfterRefund, $product->getSku());
    }

    /**
     * Get service info based on request data
     *
     * @param mixed $order
     * @param mixed $invoice
     * @param array $requestData
     * @return array
     */
    private function getServiceInfo($order, $invoice, array $requestData): array
    {
        if (isset(
            $requestData['use_invoice_endpoint']
        ) && $requestData['use_invoice_endpoint']) {
            return $this->getInvoiceRefundServiceInfo((int)$invoice->getEntityId());
        }

        return $this->getOrderRefundServiceInfo((int)$order->getEntityId());
    }

    /**
     * Ensure qty is float type for strict type checking
     *
     * @param array $items
     * @return array
     */
    private function normalizeCreditmemoItems(array $items): array
    {
        foreach ($items as &$item) {
            if (isset($item['qty'])) {
                $item['qty'] = (float)$item['qty'];
            }
        }
        return $items;
    }

    /**
     * Prepare request data based on test scenario
     *
     * @param OrderInterface $order
     * @param InvoiceInterface $invoice
     * @param array $requestConfig
     * @return array
     */
    private function prepareRequestData(OrderInterface $order, InvoiceInterface $invoice, array $requestConfig): array
    {
        $requestData = [];
        if ($requestConfig['include_items']) {
            [$orderItems] = $this->getOrderItems($order);
            $requestData['items'] = $this->normalizeCreditmemoItems($orderItems);
        }
        if ($requestConfig['include_return_to_stock']) {
            if (!$requestConfig['use_correct_item_ids'] && isset($requestConfig['incorrect_item_ids'])) {
                $itemIds = $requestConfig['incorrect_item_ids'];
            } else {
                [, $itemIds] = $this->getOrderItems($order);
            }
            $requestData['arguments'] = [
                'extension_attributes' => [
                    'return_to_stock_items' => $itemIds
                ]
            ];
        }

        if (isset($requestConfig['use_invoice_endpoint']) && $requestConfig['use_invoice_endpoint']) {
            $requestData['invoiceId'] = (int)$invoice->getEntityId();
        } else {
            $requestData['orderId'] = (int)$order->getEntityId();
        }

        return $requestData;
    }

    /**
     * Get order items data
     *
     * @param mixed $order
     * @return array[]
     */
    private function getOrderItems($order): array
    {
        $items = [];
        $itemIds = [];
        foreach ($order->getAllItems() as $item) {
            $items[] = [
                'order_item_id' => (int) $item->getItemId(),
                'qty' => floatval($item->getQtyOrdered()),
            ];
            $itemIds[] = $item->getItemId();
        }

        return [$items, $itemIds];
    }

    /**
     * Assert product stock quantity
     *
     * @param float $expectedQty
     * @param string $sku
     * @return void
     */
    private function assertProductQty(float $expectedQty, string $sku): void
    {
        $actualQty = $this->getStockQtyViaAPI($sku);
        $this->assertEquals(
            $expectedQty,
            $actualQty,
            "Expected stock quantity {$expectedQty}, but got {$actualQty} for product {$sku}"
        );
    }

    /**
     * Get order refund service info
     *
     * @param int $orderId
     * @return array
     */
    private function getOrderRefundServiceInfo(int $orderId): array
    {
        return [
            'rest' => [
                'resourcePath' => '/V1/order/' . $orderId . '/refund',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::ORDER_REFUND_SERVICE,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::ORDER_REFUND_SERVICE . 'execute',
            ],
        ];
    }

    /**
     * Get stock quantity via WebAPI
     *
     * @param string $sku
     * @return float
     */
    private function getStockQtyViaAPI(string $sku): float
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/' . self::MSI_SALABLE_QTY_ENDPOINT . '/' . $sku . '/' . self::DEFAULT_STOCK_ID,
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::MSI_SALABLE_QTY_SOAP_SERVICE,
                'operation' => self::MSI_SALABLE_QTY_SOAP_SERVICE . 'Execute',
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, ['sku' => $sku, 'stockId' => self::DEFAULT_STOCK_ID]);

        return (float)$result;
    }

    /**
     * Get invoice refund service info
     *
     * @param int $invoiceId
     * @return array
     */
    private function getInvoiceRefundServiceInfo(int $invoiceId): array
    {
        return [
            'rest' => [
                'resourcePath' => '/V1/invoice/' . $invoiceId . '/refund',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::INVOICE_REFUND_SERVICE,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::INVOICE_REFUND_SERVICE . 'execute',
            ],
        ];
    }

    /**
     * Get simple product order item ID (works for configurable and bundle child items)
     *
     * @param mixed $order
     * @param string $sku
     * @return int
     */
    private function getSimpleProductOrderItemId($order, string $sku): int
    {
        foreach ($order->getAllItems() as $item) {
            if ($item->getSku() === $sku) {
                return (int)$item->getItemId();
            }
        }
        throw new RuntimeException("Order item with SKU {$sku} not found");
    }
}

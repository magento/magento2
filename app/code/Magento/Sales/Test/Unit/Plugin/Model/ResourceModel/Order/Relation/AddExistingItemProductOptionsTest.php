<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Plugin\Model\ResourceModel\Order\Relation;

use Magento\Bundle\Api\Data\BundleOptionInterface;
use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Item as OrderItemResource;
use Magento\Sales\Model\ResourceModel\Order\Relation;
use Magento\Sales\Plugin\Model\ResourceModel\Order\Relation\AddExistingItemProductOptions;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[AllowMockObjectsWithoutExpectations]
class AddExistingItemProductOptionsTest extends TestCase
{
    private const STORE_ID = 1;

    /** @var OrderItemResource&MockObject */
    private OrderItemResource $orderItemResource;

    /** @var ProductRepositoryInterface&MockObject */
    private ProductRepositoryInterface $productRepository;

    /** @var AddExistingItemProductOptions */
    private AddExistingItemProductOptions $plugin;

    protected function setUp(): void
    {
        $this->orderItemResource = $this->createMock(OrderItemResource::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->plugin = new AddExistingItemProductOptions(
            $this->orderItemResource,
            new Json(),
            $this->productRepository,
            new DataObjectFactory()
        );
    }

    /**
     * @param array $extensionConfig
     * @param array $expectedBuyRequest
     * @param array $generatedOptions
     * @param array $expectedProductOptions
     */
    #[DataProvider('newOrderItemDataProvider')]
    public function testBeforeProcessRelationAddsGeneratedProductOptionsForNewItems(
        array $extensionConfig,
        array $expectedBuyRequest,
        array $generatedOptions,
        array $expectedProductOptions
    ): void {
        $extensionAttributes = $this->createProductOptionExtensionMock($extensionConfig);
        $productOption = $this->createMock(ProductOptionInterface::class);
        $productOption->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $item = $this->createOrderItemMock();
        $item->method('getItemId')->willReturn(null);
        $item->method('getProductOptions')->willReturn(null);
        $item->method('getQtyOrdered')->willReturn(1);
        $item->method('getProductOption')->willReturn($productOption);
        $item->method('getProductId')->willReturn(42);
        $item->method('getStoreId')->willReturn(self::STORE_ID);

        $productType = $this->createMock(AbstractType::class);
        $product = $this->createMock(Product::class);
        $product->method('getTypeInstance')->willReturn($productType);

        $this->productRepository->expects($this->once())
            ->method('getById')
            ->with(42, false, self::STORE_ID, true)
            ->willReturn($product);
        $productType->expects($this->once())
            ->method('prepareForCartAdvanced')
            ->with(
                self::callback(
                    static fn (DataObject $buyRequest): bool => $buyRequest->toArray() === $expectedBuyRequest
                ),
                $product,
                AbstractType::PROCESS_MODE_LITE
            )
            ->willReturn([$product]);
        $productType->expects($this->once())
            ->method('getOrderOptions')
            ->with($product)
            ->willReturn($generatedOptions);

        $item->expects($this->once())
            ->method('setProductOptions')
            ->with($expectedProductOptions);

        $this->plugin->beforeProcessRelation(
            $this->createMock(Relation::class),
            $this->createOrder([$item])
        );
    }

    public function testBeforeProcessRelationPreservesExistingProductOptionsForExistingItems(): void
    {
        $existingOptions = ['options' => [['label' => 'Existing', 'value' => 'Value']]];
        $connection = $this->createMock(AdapterInterface::class);
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['from', 'where'])
            ->getMock();
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();

        $this->orderItemResource->method('getConnection')->willReturn($connection);
        $this->orderItemResource->method('getMainTable')->willReturn('sales_order_item');
        $connection->method('select')->willReturn($select);
        $connection->method('fetchRow')->with($select)->willReturn([
            'product_options' => json_encode($existingOptions),
        ]);
        $this->productRepository->expects($this->never())->method('getById');

        $item = $this->createOrderItemMock();
        $item->method('getItemId')->willReturn(11);
        $item->expects($this->once())->method('setProductOptions')->with($existingOptions);

        $this->plugin->beforeProcessRelation(
            $this->createMock(Relation::class),
            $this->createOrder([$item])
        );
    }

    public function testBeforeProcessRelationKeepsInfoBuyRequestWhenProductOptionsCannotBeGenerated(): void
    {
        $extensionAttributes = $this->createProductOptionExtensionMock([
            'custom_options' => [self::createCustomOption('1', '3')],
        ]);
        $productOption = $this->createMock(ProductOptionInterface::class);
        $productOption->method('getExtensionAttributes')->willReturn($extensionAttributes);

        $item = $this->createOrderItemMock();
        $item->method('getItemId')->willReturn(null);
        $item->method('getProductOptions')->willReturn(null);
        $item->method('getQtyOrdered')->willReturn(1);
        $item->method('getProductOption')->willReturn($productOption);
        $item->method('getProductId')->willReturn(42);
        $item->method('getStoreId')->willReturn(self::STORE_ID);

        $this->productRepository->method('getById')->willThrowException(new \Exception('Product missing'));
        $item->expects($this->once())
            ->method('setProductOptions')
            ->with(['info_buyRequest' => ['qty' => 1, 'options' => ['1' => '3']]]);

        $this->plugin->beforeProcessRelation(
            $this->createMock(Relation::class),
            $this->createOrder([$item])
        );
    }

    public function testBeforeProcessRelationSkipsEnrichmentWithoutProductOptionPayload(): void
    {
        $item = $this->createOrderItemMock();
        $item->method('getItemId')->willReturn(null);
        $item->method('getProductOption')->willReturn(null);
        $item->method('getProductOptions')->willReturn([
            'info_buyRequest' => [
                'qty' => 1,
                'options' => [
                    '7' => [
                        'year' => '2024',
                        'month' => '12',
                        'day' => '25',
                    ],
                ],
            ],
        ]);

        $this->productRepository->expects($this->never())->method('getById');
        $item->expects($this->never())->method('setProductOptions');

        $this->plugin->beforeProcessRelation(
            $this->createMock(Relation::class),
            $this->createOrder([$item])
        );
    }

    public static function newOrderItemDataProvider(): array
    {
        return [
            'simple custom option' => [
                ['custom_options' => [self::createCustomOption('1', '3')]],
                ['qty' => 1, 'options' => ['1' => '3']],
                [
                    'options' => [
                        [
                            'label' => 'sizeoption',
                            'value' => 'S',
                            'print_value' => 'S',
                            'option_id' => '1',
                            'option_type' => 'drop_down',
                            'option_value' => '3',
                            'custom_view' => false,
                        ],
                    ],
                ],
                [
                    'options' => [
                        [
                            'label' => 'sizeoption',
                            'value' => 'S',
                            'print_value' => 'S',
                            'option_id' => '1',
                            'option_type' => 'drop_down',
                            'option_value' => '3',
                            'custom_view' => false,
                        ],
                    ],
                    'info_buyRequest' => ['qty' => 1, 'options' => ['1' => '3']],
                ],
            ],
            'configurable option' => [
                ['configurable_item_options' => [self::createConfigurableOption('93', 13)]],
                ['qty' => 1, 'super_attribute' => ['93' => '13']],
                [
                    'attributes_info' => [
                        ['label' => 'Color', 'value' => 'red', 'option_id' => 93, 'option_value' => '13'],
                    ],
                    'simple_name' => 'configprod-red',
                    'simple_sku' => 'configprod-red',
                ],
                [
                    'attributes_info' => [
                        ['label' => 'Color', 'value' => 'red', 'option_id' => 93, 'option_value' => '13'],
                    ],
                    'simple_name' => 'configprod-red',
                    'simple_sku' => 'configprod-red',
                    'info_buyRequest' => ['qty' => 1, 'super_attribute' => ['93' => '13']],
                ],
            ],
            'bundle option' => [
                [
                    'bundle_options' => [
                        self::createBundleOption(3, 1, [6]),
                        self::createBundleOption(4, 1, [8]),
                    ],
                ],
                ['qty' => 1, 'bundle_option' => [3 => [6], 4 => [8]], 'bundle_option_qty' => [3 => 1, 4 => 1]],
                [
                    'bundle_options' => [
                        3 => ['label' => 'choose from below 2', 'value' => [['title' => 'bundle-child1']]],
                        4 => ['label' => 'choose 1 from below 2', 'value' => [['title' => 'bundle-child-3']]],
                    ],
                    'product_calculations' => 0,
                    'shipment_type' => '0',
                ],
                [
                    'bundle_options' => [
                        3 => ['label' => 'choose from below 2', 'value' => [['title' => 'bundle-child1']]],
                        4 => ['label' => 'choose 1 from below 2', 'value' => [['title' => 'bundle-child-3']]],
                    ],
                    'product_calculations' => 0,
                    'shipment_type' => '0',
                    'info_buyRequest' => [
                        'qty' => 1,
                        'bundle_option' => [3 => [6], 4 => [8]],
                        'bundle_option_qty' => [3 => 1, 4 => 1],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array $items
     * @return Order
     */
    private function createOrder(array $items): Order
    {
        $order = $this->createPartialMock(Order::class, ['getId', 'getItems']);
        $order->method('getId')->willReturn(1);
        $order->method('getItems')->willReturn($items);

        return $order;
    }

    /**
     * @return Item&MockObject
     */
    private function createOrderItemMock(): Item
    {
        return $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getItemId',
                'getProductOptions',
                'setProductOptions',
                'getQtyOrdered',
                'getProductOption',
                'getProductId',
                'getStoreId',
            ])
            ->getMock();
    }

    /**
     * @param array $config
     * @return object
     */
    private function createProductOptionExtensionMock(array $config): object
    {
        return new class ($config) {
            /**
             * @param array $config
             */
            public function __construct(private readonly array $config)
            {
            }

            /**
             * @return CustomOptionInterface[]
             */
            public function getCustomOptions(): array
            {
                return $this->config['custom_options'] ?? [];
            }

            /**
             * @return ConfigurableItemOptionValueInterface[]
             */
            public function getConfigurableItemOptions(): array
            {
                return $this->config['configurable_item_options'] ?? [];
            }

            /**
             * @return BundleOptionInterface[]
             */
            public function getBundleOptions(): array
            {
                return $this->config['bundle_options'] ?? [];
            }
        };
    }

    private static function createCustomOption(string $optionId, string $optionValue): CustomOptionInterface
    {
        return new class($optionId, $optionValue) implements CustomOptionInterface {
            public function __construct(private readonly string $optionId, private readonly string $optionValue)
            {
            }

            public function getOptionId()
            {
                return $this->optionId;
            }

            public function setOptionId($value)
            {
                return true;
            }

            public function getOptionValue()
            {
                return $this->optionValue;
            }

            public function setOptionValue($value)
            {
                return true;
            }

            public function getExtensionAttributes()
            {
                return null;
            }

            public function setExtensionAttributes(
                \Magento\Catalog\Api\Data\CustomOptionExtensionInterface $extensionAttributes
            ) {
                return $this;
            }

            public function getCustomAttributes()
            {
                return [];
            }

            public function getCustomAttribute($attributeCode)
            {
                return null;
            }

            public function setCustomAttributes(array $attributes)
            {
                return $this;
            }

            public function setCustomAttribute($attributeCode, $attributeValue)
            {
                return $this;
            }
        };
    }

    private static function createConfigurableOption(
        string $optionId,
        int $optionValue
    ): ConfigurableItemOptionValueInterface {
        return new class($optionId, $optionValue) implements ConfigurableItemOptionValueInterface {
            public function __construct(private readonly string $optionId, private readonly int $optionValue)
            {
            }

            public function getOptionId()
            {
                return $this->optionId;
            }

            public function setOptionId($value)
            {
            }

            public function getOptionValue()
            {
                return $this->optionValue;
            }

            public function setOptionValue($value)
            {
            }

            public function getExtensionAttributes()
            {
                return null;
            }

            public function setExtensionAttributes(
                \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueExtensionInterface $extensionAttributes
            ) {
                return $this;
            }

            public function getCustomAttributes()
            {
                return [];
            }

            public function getCustomAttribute($attributeCode)
            {
                return null;
            }

            public function setCustomAttributes(array $attributes)
            {
                return $this;
            }

            public function setCustomAttribute($attributeCode, $attributeValue)
            {
                return $this;
            }
        };
    }

    private static function createBundleOption(int $optionId, int $optionQty, array $selections): BundleOptionInterface
    {
        return new class($optionId, $optionQty, $selections) implements BundleOptionInterface {
            /**
             * @param int $optionId
             * @param int $optionQty
             * @param int[] $selections
             */
            public function __construct(
                private readonly int $optionId,
                private readonly int $optionQty,
                private readonly array $selections
            ) {
            }

            public function getOptionId()
            {
                return $this->optionId;
            }

            public function setOptionId($optionId)
            {
                return $this->optionId;
            }

            public function getOptionQty()
            {
                return $this->optionQty;
            }

            public function setOptionQty($optionQty)
            {
                return $this->optionQty;
            }

            public function getOptionSelections()
            {
                return $this->selections;
            }

            public function setOptionSelections(array $optionSelections)
            {
                return $this->selections;
            }

            public function getExtensionAttributes()
            {
                return null;
            }

            public function setExtensionAttributes(
                \Magento\Bundle\Api\Data\BundleOptionExtensionInterface $extensionAttributes
            ) {
                return $this;
            }

            public function getCustomAttributes()
            {
                return [];
            }

            public function getCustomAttribute($attributeCode)
            {
                return null;
            }

            public function setCustomAttributes(array $attributes)
            {
                return $this;
            }

            public function setCustomAttribute($attributeCode, $attributeValue)
            {
                return $this;
            }
        };
    }
}

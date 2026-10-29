<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute\Backend\TierPrice;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\DataObject;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\SaveHandler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SaveHandler|MockObject
     */
    private $saveHandler;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $attributeRepository;

    /**
     * @var GroupManagementInterface|MockObject
     */
    private $groupManagement;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoll;

    /**
     * @var Tierprice|MockObject
     */
    private $tierPriceResource;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->attributeRepository = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->groupManagement = $this->createMock(GroupManagementInterface::class);
        $this->metadataPoll = $this->createPartialMock(MetadataPool::class, ['getMetadata']);
        $this->tierPriceResource = $this->createPartialMock(Tierprice::class, ['savePriceData', 'loadPriceData']);

        $this->saveHandler = $this->objectManager->getObject(
            SaveHandler::class,
            [
                'storeManager' => $this->storeManager,
                'attributeRepository' => $this->attributeRepository,
                'groupManagement' => $this->groupManagement,
                'metadataPoll' => $this->metadataPoll,
                'tierPriceResource' => $this->tierPriceResource
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $tierPrices = [
            ['website_id' => 0, 'price_qty' => 2, 'cust_group' => 0, 'price' => 10],
            ['website_id' => 0, 'price_qty' => 3, 'cust_group' => 3200, 'price' => null, 'percentage_value' => 20]
        ];
        $linkField = 'entity_id';
        $productId = 10;

        /** @var ProductInterface $product */
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['setData', 'getData', 'setStoreId', 'getStoreId']
        );
        $productData = ['tier_price' => $tierPrices, 'entity_id' => $productId, 'tier_price_changed' => 1];
        $product->method('setData')->willReturnCallback(function ($key, $value) use (&$productData) {
            $productData[$key] = $value;
            return $productData;
        });
        $product->method('getData')->willReturnCallback(function ($key = null) use (&$productData) {
            return $key === null ? $productData : ($productData[$key] ?? null);
        });
        $product->method('setStoreId')->willReturnSelf();
        $product->method('getStoreId')->willReturn(0);
        
        $product->setData('tier_price', $tierPrices);
        $product->setData('entity_id', $productId);
        $product->setStoreId(0);
        $product->setData('tier_price_changed', 1);
        
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn(0);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        
        /** @var ProductAttributeInterface $attribute */
        $attribute = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['getIsScopeGlobal', 'isScopeGlobal', 'getName', '_construct']
        );
        $attribute->method('getIsScopeGlobal')->willReturn(true);
        $attribute->method('isScopeGlobal')->willReturn(true);
        $attribute->method('getName')->willReturn('tier_price');
        
        $this->attributeRepository->expects($this->atLeastOnce())->method('get')->with('tier_price')
            ->willReturn($attribute);
        $productMetadata = $this->createMock(EntityMetadataInterface::class);
        $productMetadata->expects($this->atLeastOnce())->method('getLinkField')->willReturn($linkField);
        $this->metadataPoll->expects($this->atLeastOnce())->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($productMetadata);
        $customerGroup = $this->createMock(GroupInterface::class);
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn(3200);
        $this->groupManagement->expects($this->atLeastOnce())->method('getAllCustomersGroup')
            ->willReturn($customerGroup);
        $this->tierPriceResource->expects($this->atLeastOnce())->method('savePriceData')->willReturnSelf();

        $this->assertEquals($product, $this->saveHandler->execute($product));
    }

    /**
     * @return void
     */
    public function testExecuteWithException(): void
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Tier prices data should be array, but actually other type is received');
        
        /** @var ProductAttributeInterface $attribute */
        $attribute = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['getName', '_construct']
        );
        $attribute->method('getName')->willReturn('tier_price');
        
        $this->attributeRepository->expects($this->atLeastOnce())->method('get')->with('tier_price')
            ->willReturn($attribute);
        
        /** @var ProductInterface $product */
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['setData', 'getData']
        );
        $productData = ['tier_price' => 1];
        $product->method('setData')->willReturnCallback(function ($key, $value) use (&$productData) {
            $productData[$key] = $value;
        });
        $product->method('getData')->willReturnCallback(function ($key = null) use (&$productData) {
            return $key === null ? $productData : ($productData[$key] ?? null);
        });
        $product->setData('tier_price', 1);

        $this->saveHandler->execute($product);
    }

    /**
     * @param array $tierPrices
     * @param array $tierPricesStored
     * @param array $tierPricesExpected
     *
     * @return void
     */
    #[DataProvider('executeWithWebsitePriceDataProvider')]
    public function testExecuteWithWebsitePrice(
        array $tierPrices,
        array $tierPricesStored,
        array $tierPricesExpected
    ): void {
        $productId = 10;
        $linkField = 'entity_id';

        /** @var ProductInterface $product */
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['setData', 'getData', 'setStoreId', 'getStoreId']
        );
        $productData = ['tier_price' => $tierPrices, 'entity_id' => $productId, 'tier_price_changed' => 1];
        $product->method('setData')->willReturnCallback(function ($key, $value) use (&$productData) {
            $productData[$key] = $value;
            return $productData;
        });
        $product->method('getData')->willReturnCallback(function ($key = null) use (&$productData) {
            return $key === null ? $productData : ($productData[$key] ?? null);
        });
        $product->method('setStoreId')->willReturnSelf();
        $product->method('getStoreId')->willReturn(0);
        
        $product->setData('tier_price', $tierPrices);
        $product->setData('entity_id', $productId);
        $product->setStoreId(0);
        $product->setData('tier_price_changed', 1);
        
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn(1);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        
        /** @var ProductAttributeInterface $attribute */
        $attribute = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['getIsScopeGlobal', 'isScopeGlobal', 'getName', '_construct']
        );
        $attribute->method('getIsScopeGlobal')->willReturn(false);
        $attribute->method('isScopeGlobal')->willReturn(false);
        $attribute->method('getName')->willReturn('tier_price');
        
        $this->attributeRepository->expects($this->atLeastOnce())->method('get')->with('tier_price')
            ->willReturn($attribute);
        $productMetadata = $this->createMock(EntityMetadataInterface::class);
        $productMetadata->expects($this->atLeastOnce())->method('getLinkField')->willReturn($linkField);
        $this->metadataPoll->expects($this->atLeastOnce())->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($productMetadata);
        $customerGroup = $this->createMock(GroupInterface::class);
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn(3200);
        $this->groupManagement->expects($this->atLeastOnce())->method('getAllCustomersGroup')
            ->willReturn($customerGroup);
        $this->tierPriceResource
            ->method('savePriceData')
            ->willReturnCallback(function (...$args) use ($tierPricesExpected) {
                static $index = 0;
                $expectedArgs = [
                    [new DataObject($tierPricesExpected[0])],
                    [new DataObject($tierPricesExpected[1])],
                    [new DataObject($tierPricesExpected[2])]
                ];
                $returnValue = $this->tierPriceResource;
                $index++;
                return $args === $expectedArgs[$index - 1] ? $returnValue : null;
            });
        
        $this->tierPriceResource
            ->expects($this->atLeastOnce())
            ->method('loadPriceData')
            ->willReturn($tierPricesStored);

        $this->assertEquals($product, $this->saveHandler->execute($product));
    }

    /**
     * @return array
     */
    public static function executeWithWebsitePriceDataProvider(): array
    {
        $productId = 10;
        return [[
            'tierPrices' => [
                [
                    'price_id' => 1,
                    'website_id' => 0,
                    'price_qty' => 1,
                    'cust_group' => 0,
                    'price' => 10,
                    'product_id' => $productId
                ],
                [
                    'price_id' => 2,
                    'website_id' => 1,
                    'price_qty' => 2,
                    'cust_group' => 3200,
                    'price' => null,
                    'percentage_value' => 20,
                    'product_id' => $productId
                ]
            ],
            'tierPricesStored' => [
                [
                    'price_id' => 3,
                    'website_id' => 1,
                    'price_qty' => 3,
                    'cust_group' => 0,
                    'price' => 30,
                    'product_id' => $productId
                ]
            ],
            'tierPricesExpected' => [
                [
                    'website_id' => 0,
                    'qty' => 1,
                    'customer_group_id' => 0,
                    'all_groups' => 0,
                    'value' => 10,
                    'percentage_value' => null,
                    'entity_id' => $productId
                ],
                [
                    'website_id' => 1,
                    'qty' => 2,
                    'customer_group_id' => 0,
                    'all_groups' => 1,
                    'value' => null,
                    'percentage_value' => 20,
                    'entity_id' => $productId
                ],
                [
                    'website_id' => 1,
                    'qty' => 3,
                    'customer_group_id' => 0,
                    'all_groups' => 0,
                    'value' => 30,
                    'percentage_value' => null,
                    'entity_id' => $productId
                ]
            ]
        ]];
    }
}

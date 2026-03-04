<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute\Backend\TierPrice;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\InputException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for \Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateHandlerTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var UpdateHandler|MockObject
     */
    private $updateHandler;

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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->attributeRepository = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->groupManagement = $this->createMock(GroupManagementInterface::class);
        $this->metadataPoll = $this->createPartialMock(MetadataPool::class, ['getMetadata']);
        $this->tierPriceResource = $this->createMock(Tierprice::class);

        $this->updateHandler = $this->objectManager->getObject(
            UpdateHandler::class,
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
     * Verify update handle.
     *
     * @param array $newTierPrices
     * @param array $originalTierPrices
     * @param int $priceIdToDelete
     * @param string $linkField
     * @param int $productId
     * @param int $originalProductId
     * @throws InputException
     */
    #[DataProvider('configDataProvider')]
    public function testExecute(
        $newTierPrices,
        $originalTierPrices,
        $priceIdToDelete,
        $linkField,
        $productId,
        $originalProductId
    ): void {

        /** @var ProductInterface $product */
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['setData', 'getData', 'setOrigData', 'getOrigData', 'setStoreId', 'getStoreId']
        );
        $productData = ['tier_price' => $newTierPrices, 'entity_id' => $productId, 'tier_price_changed' => 1];
        $productOrigData = ['tier_price' => $originalTierPrices, 'entity_id' => $originalProductId];
        $product->method('setData')->willReturnCallback(function ($key, $value) use (&$productData) {
            $productData[$key] = $value;
        });
        $product->method('getData')->willReturnCallback(function ($key = null) use (&$productData) {
            return $key === null ? $productData : ($productData[$key] ?? null);
        });
        $product->method('setOrigData')->willReturnCallback(function ($key, $value) use (&$productOrigData) {
            $productOrigData[$key] = $value;
        });
        $product->method('getOrigData')->willReturnCallback(function ($key = null) use (&$productOrigData) {
            return $key === null ? $productOrigData : ($productOrigData[$key] ?? null);
        });
        $product->method('setStoreId')->willReturnSelf();
        $product->method('getStoreId')->willReturn(0);
        
        $product->setData('tier_price', $newTierPrices);
        $product->setData('entity_id', $productId);
        $product->setOrigData('tier_price', $originalTierPrices);
        $product->setOrigData('entity_id', $originalProductId);
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
        $this->tierPriceResource->expects($this->exactly(2))->method('savePriceData')->willReturnSelf();
        $this->tierPriceResource->expects($this->once())->method('deletePriceData')
            ->with($productId, null, $priceIdToDelete);
        $this->assertEquals($product, $this->updateHandler->execute($product));
    }

    /**
     * Verify update handle with exception.
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

        $this->updateHandler->execute($product);
    }

    /**
     * Returns test parameters.
     *
     * @return array
     */
    public static function configDataProvider()
    {
        return [
            [
                [
                    [
                        'website_id' => 0,
                        'price_qty' => 2,
                        'cust_group' => 0,
                        'price' => 15
                    ],
                    [
                        'website_id' => 0,
                        'price_qty' => 3,
                        'cust_group' => 3200,
                        'price' => null,
                        'percentage_value' => 20
                    ]
                ],
                [
                    [
                        'price_id' => 1,
                        'website_id' => 0,
                        'price_qty' => 2,
                        'cust_group' => 0,
                        'price' => 10],
                    [
                        'price_id' => 2,
                        'website_id' => 0,
                        'price_qty' => 4,
                        'cust_group' => 0,
                        'price' => 20
                    ],
                ],
                2,
                'entity_id',
                10,
                11
            ],
            [
                [
                    [
                        'website_id' => 0,
                        'price_qty' => 2,
                        'cust_group' => 0,
                        'price' => 0
                    ],
                    [
                        'website_id' => 0,
                        'price_qty' => 3,
                        'cust_group' => 3200,
                        'price' => null,
                        'percentage_value' => 20
                    ]
                ],
                [
                    [
                        'price_id' => 1,
                        'website_id' => 0,
                        'price_qty' => 2,
                        'cust_group' => 0,
                        'price' => 10
                    ],
                    [
                        'price_id' => 2,
                        'website_id' => 0,
                        'price_qty' => 4,
                        'cust_group' => 0,
                        'price' => 20
                    ],
                ],
                2,
                'entity_id',
                10,
                11
            ]
        ];
    }
}

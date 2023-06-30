<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute\Backend\TierPrice;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\SaveHandler;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
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
    /**
     * Magento\Framework\TestFramework\Unit\Helper\ObjectManager
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->attributeRepository = $this->getMockBuilder(ProductAttributeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->groupManagement = $this->getMockBuilder(GroupManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllCustomersGroup'])
            ->getMockForAbstractClass();
        $this->metadataPoll = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMetadata'])
            ->getMock();
        $this->tierPriceResource = $this->getMockBuilder(Tierprice::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

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

    public function testExecute(): void
    {
        $tierPrices = [
            ['website_id' => 0, 'price_qty' => 2, 'cust_group' => 0, 'price' => 10],
            ['website_id' => 0, 'price_qty' => 3, 'cust_group' => 3200, 'price' => null, 'percentage_value' => 20]
        ];
        $linkField = 'entity_id';
        $productId = 10;

        /** @var MockObject $product */
        $product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData','setData', 'getStoreId'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getData')->willReturnMap(
            [
                ['tier_price', $tierPrices],
                ['entity_id', $productId]
            ]
        );
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn(0);
        $product->expects($this->atLeastOnce())->method('setData')->with('tier_price_changed', 1);
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn(0);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        /** @var MockObject $attribute */
        $attribute = $this->getMockBuilder(ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'isScopeGlobal'])
            ->getMockForAbstractClass();
        $attribute->expects($this->atLeastOnce())->method('getName')->willReturn('tier_price');
        $attribute->expects($this->atLeastOnce())->method('isScopeGlobal')->willReturn(true);
        $this->attributeRepository->expects($this->atLeastOnce())->method('get')->with('tier_price')
            ->willReturn($attribute);
        $productMetadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMockForAbstractClass();
        $productMetadata->expects($this->atLeastOnce())->method('getLinkField')->willReturn($linkField);
        $this->metadataPoll->expects($this->atLeastOnce())->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($productMetadata);
        $customerGroup = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn(3200);
        $this->groupManagement->expects($this->atLeastOnce())->method('getAllCustomersGroup')
            ->willReturn($customerGroup);
        $this->tierPriceResource->expects($this->atLeastOnce())->method('savePriceData')->willReturnSelf();

        $this->assertEquals($product, $this->saveHandler->execute($product));
    }

    public function testExecuteWithException(): void
    {
        $this->expectException('Magento\Framework\Exception\InputException');
        $this->expectExceptionMessage('Tier prices data should be array, but actually other type is received');
        /** @var MockObject $attribute */
        $attribute = $this->getMockBuilder(ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'isScopeGlobal'])
            ->getMockForAbstractClass();
        $attribute->expects($this->atLeastOnce())->method('getName')->willReturn('tier_price');
        $this->attributeRepository->expects($this->atLeastOnce())->method('get')->with('tier_price')
            ->willReturn($attribute);
        /** @var MockObject $product */
        $product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData','setData', 'getStoreId', 'getOrigData'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getData')->with('tier_price')->willReturn(1);

        $this->saveHandler->execute($product);
    }

    /**
     * @param $tierPrices
     * @param $tierPricesStored
     * @param $tierPricesExpected
     * @dataProvider executeWithWebsitePriceDataProvider
     */
    public function testExecuteWithWebsitePrice($tierPrices, $tierPricesStored, $tierPricesExpected): void
    {
        $productId = 10;
        $linkField = 'entity_id';

        /** @var MockObject $product */
        $product = $this->getMockBuilder(ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData','setData', 'getStoreId'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getData')->willReturnMap(
            [
                ['tier_price', $tierPrices],
                ['entity_id', $productId]
            ]
        );
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn(0);
        $product->expects($this->atLeastOnce())->method('setData')->with('tier_price_changed', 1);
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn(1);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        /** @var MockObject $attribute */
        $attribute = $this->getMockBuilder(ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'isScopeGlobal'])
            ->getMockForAbstractClass();
        $attribute->expects($this->atLeastOnce())->method('getName')->willReturn('tier_price');
        $attribute->expects($this->atLeastOnce())->method('isScopeGlobal')->willReturn(false);
        $this->attributeRepository->expects($this->atLeastOnce())->method('get')->with('tier_price')
            ->willReturn($attribute);
        $productMetadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMockForAbstractClass();
        $productMetadata->expects($this->atLeastOnce())->method('getLinkField')->willReturn($linkField);
        $this->metadataPoll->expects($this->atLeastOnce())->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($productMetadata);
        $customerGroup = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn(3200);
        $this->groupManagement->expects($this->atLeastOnce())->method('getAllCustomersGroup')
            ->willReturn($customerGroup);
        $this->tierPriceResource
            ->expects($this->at(1))
            ->method('savePriceData')
            ->with(new \Magento\Framework\DataObject($tierPricesExpected[0]))
            ->willReturnSelf();
        $this->tierPriceResource
            ->expects($this->at(2))
            ->method('savePriceData')
            ->with(new \Magento\Framework\DataObject($tierPricesExpected[1]))
            ->willReturnSelf();
        $this->tierPriceResource
            ->expects($this->at(3))
            ->method('savePriceData')
            ->with(new \Magento\Framework\DataObject($tierPricesExpected[2]))
            ->willReturnSelf();
        $this->tierPriceResource
            ->expects($this->atLeastOnce())
            ->method('loadPriceData')
            ->willReturn($tierPricesStored);

        $this->assertEquals($product, $this->saveHandler->execute($product));
    }

    public function executeWithWebsitePriceDataProvider(): array
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
                ],[
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
                ],[
                    'website_id' => 1,
                    'qty' => 2,
                    'customer_group_id' => 0,
                    'all_groups' => 1,
                    'value' => null,
                    'percentage_value' => 20,
                    'entity_id' => $productId
                ],[
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

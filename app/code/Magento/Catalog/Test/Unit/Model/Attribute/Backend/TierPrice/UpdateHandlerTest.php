<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Attribute\Backend\TierPrice;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\Tierprice;

/**
 * Unit tests for \Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var UpdateHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    private $updateHandler;

    /**
     * @var StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeManager;

    /**
     * @var ProductAttributeRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeRepository;

    /**
     * @var GroupManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $groupManagement;

    /**
     * @var MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPoll;

    /**
     * @var Tierprice|\PHPUnit\Framework\MockObject\MockObject
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

        $this->updateHandler = $this->objectManager->getObject(
            \Magento\Catalog\Model\Product\Attribute\Backend\TierPrice\UpdateHandler::class,
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
        $newTierPrices = [
            ['website_id' => 0, 'price_qty' => 2, 'cust_group' => 0, 'price' => 15],
            ['website_id' => 0, 'price_qty' => 3, 'cust_group' => 3200, 'price' => null, 'percentage_value' => 20]
        ];
        $priceIdToDelete = 2;
        $originalTierPrices = [
            ['price_id' => 1, 'website_id' => 0, 'price_qty' => 2, 'cust_group' => 0, 'price' => 10],
            ['price_id' => $priceIdToDelete, 'website_id' => 0, 'price_qty' => 4, 'cust_group' => 0, 'price' => 20],
        ];
        $linkField = 'entity_id';
        $productId = 10;
        $originalProductId = 11;

        /** @var \PHPUnit\Framework\MockObject\MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData','setData', 'getStoreId', 'getOrigData'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getData')->willReturnMap(
            [
                ['tier_price', $newTierPrices],
                ['entity_id', $productId]
            ]
        );
        $product->expects($this->atLeastOnce())->method('getOrigData')
            ->willReturnMap(
                [
                    ['tier_price', $originalTierPrices],
                    ['entity_id', $originalProductId]
                ]
            );
        $product->expects($this->atLeastOnce())->method('getStoreId')->willReturn(0);
        $product->expects($this->atLeastOnce())->method('setData')->with('tier_price_changed', 1);
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteId'])
            ->getMockForAbstractClass();
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn(0);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        /** @var \PHPUnit\Framework\MockObject\MockObject $attribute */
        $attribute = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'isScopeGlobal'])
            ->getMockForAbstractClass();
        $attribute->expects($this->atLeastOnce())->method('getName')->willReturn('tier_price');
        $attribute->expects($this->atLeastOnce())->method('isScopeGlobal')->willReturn(true);
        $this->attributeRepository->expects($this->atLeastOnce())->method('get')->with('tier_price')
            ->willReturn($attribute);
        $productMetadata = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLinkField'])
            ->getMockForAbstractClass();
        $productMetadata->expects($this->atLeastOnce())->method('getLinkField')->willReturn($linkField);
        $this->metadataPoll->expects($this->atLeastOnce())->method('getMetadata')
            ->with(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->willReturn($productMetadata);
        $customerGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn(3200);
        $this->groupManagement->expects($this->atLeastOnce())->method('getAllCustomersGroup')
            ->willReturn($customerGroup);
        $this->tierPriceResource->expects($this->exactly(2))->method('savePriceData')->willReturnSelf();
        $this->tierPriceResource->expects($this->once())->method('deletePriceData')
            ->with($productId, null, $priceIdToDelete);

        $this->assertEquals($product, $this->updateHandler->execute($product));
    }

    /**
     */
    public function testExecuteWithException(): void
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Tier prices data should be array, but actually other type is received');

        /** @var \PHPUnit\Framework\MockObject\MockObject $attribute */
        $attribute = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductAttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'isScopeGlobal'])
            ->getMockForAbstractClass();
        $attribute->expects($this->atLeastOnce())->method('getName')->willReturn('tier_price');
        $this->attributeRepository->expects($this->atLeastOnce())->method('get')->with('tier_price')
            ->willReturn($attribute);
        /** @var \PHPUnit\Framework\MockObject\MockObject $product */
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData','setData', 'getStoreId', 'getOrigData'])
            ->getMockForAbstractClass();
        $product->expects($this->atLeastOnce())->method('getData')->with('tier_price')->willReturn(1);

        $this->updateHandler->execute($product);
    }
}

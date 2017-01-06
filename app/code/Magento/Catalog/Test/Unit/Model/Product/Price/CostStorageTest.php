<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

/**
 * Class CostStorageTest.
 */
class CostStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Price\PricePersistenceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pricePersistenceFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Price\PricePersistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pricePersistence;

    /**
     * @var \Magento\Catalog\Api\Data\CostInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $costInterfaceFactory;

    /**
     * @var \Magento\Catalog\Api\Data\CostInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $costInterface;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productIdLocator;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Price\InvalidSkuChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invalidSkuChecker;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResult;

    /**
     * @var \Magento\Catalog\Model\Product\Price\CostStorage
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->pricePersistenceFactory = $this->getMock(
            \Magento\Catalog\Model\Product\Price\PricePersistenceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->pricePersistence = $this->getMock(
            \Magento\Catalog\Model\Product\Price\PricePersistence::class,
            ['get', 'retrieveSkuById', 'update', 'delete', 'getEntityLinkField'],
            [],
            '',
            false
        );
        $this->costInterfaceFactory = $this->getMock(
            \Magento\Catalog\Api\Data\CostInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->costInterface = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\Data\CostInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setSku', 'setCost', 'setStoreId', 'getSku', 'getCost', 'getStoreId']
        );
        $this->productIdLocator = $this->getMockForAbstractClass(
            \Magento\Catalog\Model\ProductIdLocatorInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['retrieveProductIdsBySkus']
        );
        $this->storeRepository = $this->getMockForAbstractClass(
            \Magento\Store\Api\StoreRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
        $this->validationResult = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invalidSkuChecker = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\InvalidSkuChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\CostStorage::class,
            [
                'pricePersistenceFactory' => $this->pricePersistenceFactory,
                'costInterfaceFactory' => $this->costInterfaceFactory,
                'productIdLocator' => $this->productIdLocator,
                'storeRepository' => $this->storeRepository,
                'validationResult' => $this->validationResult,
                'invalidSkuChecker' => $this->invalidSkuChecker,
                'allowedProductTypes' => ['simple', 'virtual', 'downloadable'],
            ]
        );
    }

    /**
     * Test get method.
     *
     * @return void
     */
    public function testGet()
    {
        $skus = ['sku_1', 'sku_2'];
        $rawPrices = [
            [
                'row_id' => 1,
                'value' => 15,
                'store_id' => 1
            ],
            [
                'row_id' => 2,
                'value' => 35,
                'store_id' => 1
            ]
        ];
        $this->invalidSkuChecker
            ->expects($this->atLeastOnce())
            ->method('isSkuListValid');
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'cost'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->once())->method('get')->with($skus)->willReturn($rawPrices);
        $this->costInterfaceFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->costInterface);
        $this->pricePersistence
            ->expects($this->exactly(2))
            ->method('retrieveSkuById')
            ->willReturnOnConsecutiveCalls('sku_1', 'sku_2');
        $this->pricePersistence->expects($this->atLeastOnce())->method('getEntityLinkField')->willReturn('row_id');
        $this->costInterface
            ->expects($this->exactly(2))
            ->method('setSku')
            ->withConsecutive(['sku_1'], ['sku_2'])
            ->willReturnSelf();
        $this->costInterface
            ->expects($this->exactly(2))
            ->method('setCost')
            ->withConsecutive([15], [35])
            ->willReturnSelf();
        $this->costInterface
            ->expects($this->exactly(2))
            ->method('setStoreId')
            ->withConsecutive([1], [1])
            ->willReturnSelf();
        $this->model->get($skus);
    }

    /**
     * Test update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $store = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\StoreInterface::class,
            [],
            '',
            false
        );
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' =>
                [
                    1 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
                ]
        ];
        $this->costInterface->expects($this->exactly(5))->method('getSku')->willReturn($sku);
        $this->productIdLocator
            ->expects($this->exactly(1))
            ->method('retrieveProductIdsBySkus')->with([$sku])
            ->willReturn($idsBySku);
        $this->costInterface->expects($this->exactly(3))->method('getCost')->willReturn(15);
        $this->invalidSkuChecker
            ->expects($this->exactly(1))
            ->method('retrieveInvalidSkuList')
            ->willReturn([]);
        $this->costInterface->expects($this->exactly(2))->method('getStoreId')->willReturn(1);
        $this->pricePersistence->expects($this->atLeastOnce())->method('getEntityLinkField')->willReturn('row_id');
        $this->storeRepository->expects($this->once())->method('getById')->with(1)->willReturn($store);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'cost'])
            ->willReturn($this->pricePersistence);
        $this->validationResult
            ->expects($this->exactly(1))
            ->method('getFailedRowIds')
            ->willReturn([]);
        $formattedPrices = [
            [
                'store_id' => 1,
                'row_id' => 1,
                'value' => 15
            ]
        ];
        $this->pricePersistence->expects($this->once())->method('update')->with($formattedPrices);
        $this->validationResult
            ->expects($this->exactly(1))
            ->method('getFailedItems')
            ->willReturn([]);
        $this->assertEmpty($this->model->update([$this->costInterface]));
    }

    /**
     * Test update method with negative cost.
     */
    public function testUpdateWithNegativeCost()
    {
        $sku = 'sku_1';
        $this->costInterface->expects($this->exactly(3))->method('getSku')->willReturn($sku);
        $this->invalidSkuChecker
            ->expects($this->exactly(1))
            ->method('retrieveInvalidSkuList')
            ->willReturn([]);
        $this->validationResult
            ->expects($this->exactly(1))
            ->method('getFailedRowIds')
            ->willReturn([0]);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'cost'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->atLeastOnce())->method('update');
        $this->costInterface->expects($this->exactly(3))->method('getCost')->willReturn(-15);
        $this->validationResult
            ->expects($this->atLeastOnce())
            ->method('getFailedItems');
        $this->model->update([$this->costInterface]);
    }

    /**
     * Test delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $skus = ['sku_1', 'sku_2'];
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'cost'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->once())->method('delete')->with($skus);
        $this->invalidSkuChecker
            ->expects($this->exactly(1))
            ->method('isSkuListValid')
            ->willReturn(true);
        $this->model->delete($skus);
    }
}

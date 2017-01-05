<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

/**
 * Class BasePriceStorageTest.
 */
class BasePriceStorageTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Catalog\Api\Data\BasePriceInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $basePriceInterfaceFactory;

    /**
     * @var \Magento\Catalog\Api\Data\BasePriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $basePriceInterface;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productIdLocator;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\Product\Price\BasePriceStorage
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
            ['get', 'retrieveSkuById', 'update', 'getEntityLinkField'],
            [],
            '',
            false
        );
        $this->basePriceInterfaceFactory = $this->getMock(
            \Magento\Catalog\Api\Data\BasePriceInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->basePriceInterface = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\Data\BasePriceInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setSku', 'setPrice', 'setStoreId', 'getSku', 'getPrice', 'getStoreId']
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
        $this->productRepository = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\ProductRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['get']
        );
        $this->product = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\Data\ProductInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getPriceType']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\BasePriceStorage::class,
            [
                'pricePersistenceFactory' => $this->pricePersistenceFactory,
                'basePriceInterfaceFactory' => $this->basePriceInterfaceFactory,
                'productIdLocator' => $this->productIdLocator,
                'storeRepository' => $this->storeRepository,
                'productRepository' => $this->productRepository,
                'allowedProductTypes' => ['simple', 'virtual', 'bundle', 'downloadable'],
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
        $idsBySku = [
            'sku_1' =>
                [
                    1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
                ],
            'sku_2' =>
                [
                    2 => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                ]
        ];
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
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')->with($skus)
            ->willReturn($idsBySku);
        $this->productRepository->expects($this->once())->method('get')->willReturn($this->product);
        $this->product->expects($this->once())->method('getPriceType')->willReturn(1);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'price'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->once())->method('get')->with($skus)->willReturn($rawPrices);
        $this->pricePersistence->expects($this->atLeastOnce())->method('getEntityLinkField')->willReturn('row_id');
        $this->basePriceInterfaceFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->basePriceInterface);
        $this->pricePersistence
            ->expects($this->exactly(2))
            ->method('retrieveSkuById')
            ->willReturnOnConsecutiveCalls('sku_1', 'sku_2');
        $this->basePriceInterface
            ->expects($this->exactly(2))
            ->method('setSku')
            ->withConsecutive(['sku_1'], ['sku_2'])
            ->willReturnSelf();
        $this->basePriceInterface
            ->expects($this->exactly(2))
            ->method('setPrice')
            ->withConsecutive([15], [35])
            ->willReturnSelf();
        $this->basePriceInterface
            ->expects($this->exactly(2))
            ->method('setStoreId')
            ->withConsecutive([1], [1])
            ->willReturnSelf();
        $this->model->get($skus);
    }

    /**
     * Test get method with exception.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested products don't exist: sku_1, sku_2
     */
    public function testGetWithException()
    {
        $skus = ['sku_1', 'sku_2'];
        $idsBySku = [
            'sku_1' =>
                [
                    1 => 'configurable'
                ],
            'sku_2' =>
                [
                    2 => 'grouped'
                ]
        ];
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')->with($skus)
            ->willReturn($idsBySku);
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
                    1 => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                ]
        ];
        $this->basePriceInterface->expects($this->exactly(4))->method('getSku')->willReturn($sku);
        $this->productIdLocator
            ->expects($this->exactly(2))
            ->method('retrieveProductIdsBySkus')->with([$sku])
            ->willReturn($idsBySku);
        $this->productRepository->expects($this->once())->method('get')->willReturn($this->product);
        $this->product->expects($this->once())->method('getPriceType')->willReturn(1);
        $this->basePriceInterface->expects($this->exactly(3))->method('getPrice')->willReturn(15);
        $this->basePriceInterface->expects($this->exactly(2))->method('getStoreId')->willReturn(1);
        $this->pricePersistence->expects($this->atLeastOnce())->method('getEntityLinkField')->willReturn('row_id');
        $this->storeRepository->expects($this->once())->method('getById')->with(1)->willReturn($store);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'price'])
            ->willReturn($this->pricePersistence);
        $formattedPrices = [
            [
                'store_id' => 1,
                'row_id' => 1,
                'value' => 15
            ]
        ];
        $this->pricePersistence->expects($this->once())->method('update')->with($formattedPrices);
        $this->assertTrue($this->model->update([$this->basePriceInterface]));
    }

    /**
     * Test update method without SKU.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid attribute sku: .
     */
    public function testUpdateWithoutSku()
    {
        $this->basePriceInterface->expects($this->exactly(2))->method('getSku')->willReturn(null);
        $this->model->update([$this->basePriceInterface]);
    }

    /**
     * Test update method with negative price.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid attribute Price: -15.
     */
    public function testUpdateWithNegativePrice()
    {
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' =>
                [
                    1 => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
                ]
        ];
        $this->basePriceInterface->expects($this->exactly(2))->method('getSku')->willReturn($sku);
        $this->productIdLocator
            ->expects($this->once(1))
            ->method('retrieveProductIdsBySkus')->with([$sku])
            ->willReturn($idsBySku);
        $this->productRepository->expects($this->once())->method('get')->willReturn($this->product);
        $this->product->expects($this->once())->method('getPriceType')->willReturn(1);
        $this->basePriceInterface->expects($this->exactly(3))->method('getPrice')->willReturn(-15);
        $this->model->update([$this->basePriceInterface]);
    }
}

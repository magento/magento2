<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;
use Magento\Catalog\Api\SpecialPriceInterface;
use Magento\Catalog\Model\Product\Price\SpecialPriceStorage;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Price\Validation\Result;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\StoreRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for SpecialPriceStorage model.
 */
class SpecialPriceStorageTest extends TestCase
{
    /**
     * @var SpecialPriceInterface|MockObject
     */
    private $specialPriceResource;

    /**
     * @var SpecialPriceInterfaceFactory|MockObject
     */
    private $specialPriceFactory;

    /**
     * @var ProductIdLocatorInterface|MockObject
     */
    private $productIdLocator;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    private $storeRepository;

    /**
     * @var InvalidSkuProcessor|MockObject
     */
    private $invalidSkuProcessor;

    /**
     * @var Result|MockObject
     */
    private $validationResult;

    /**
     * @var SpecialPriceStorage
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->specialPriceResource = $this->getMockBuilder(SpecialPriceInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get', 'update', 'delete', 'getEntityLinkField'])->getMockForAbstractClass();
        $this->productIdLocator = $this->getMockBuilder(ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeRepository = $this->getMockBuilder(StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->invalidSkuProcessor = $this
            ->getMockBuilder(InvalidSkuProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->specialPriceFactory = $this->getMockBuilder(
            SpecialPriceInterfaceFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            SpecialPriceStorage::class,
            [
                'specialPriceResource' => $this->specialPriceResource,
                'specialPriceFactory' => $this->specialPriceFactory,
                'productIdLocator' => $this->productIdLocator,
                'storeRepository' => $this->storeRepository,
                'invalidSkuProcessor' => $this->invalidSkuProcessor,
                'validationResult' => $this->validationResult,
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
                'entity_id' => 1,
                'value' => 15,
                'store_id' => 1,
                'sku' => 'sku_1',
                'price_from' => '2016-12-20 01:02:03',
                'price_to' => '2016-12-21 01:02:03',
            ],
            [
                'entity_id' => 2,
                'value' => 15,
                'store_id' => 1,
                'price_from' => '2016-12-20 01:02:03',
                'price_to' => '2016-12-21 01:02:03',
            ],
            [
                'entity_id' => 3,
                'value' => 15,
                'store_id' => 1,
                'price_from' => '2016-12-20 01:02:03',
                'price_to' => '2016-12-21 01:02:03',
            ],
        ];
        $this->invalidSkuProcessor->expects($this->once())->method('filterSkuList')->with($skus, [])->willReturn($skus);
        $this->specialPriceResource->expects($this->once())->method('get')->willReturn($rawPrices);
        $this->specialPriceResource->expects($this->atLeastOnce())
            ->method('getEntityLinkField')->willReturn('entity_id');
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\SpecialPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $price->expects($this->exactly(3))->method('setPrice');
        $this->specialPriceFactory->expects($this->atLeastOnce())->method('create')->willReturn($price);
        $this->productIdLocator->expects($this->atLeastOnce())->method('retrieveProductIdsBySkus')->willReturn(
            [
                'sku_2' => [2 => 'prod']
            ]
        );
        $this->model->get($skus);
    }

    /**
     * Test update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\SpecialPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $prices = [1 => $price];
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('sku_1');
        $price->expects($this->atLeastOnce())->method('getPrice')->willReturn(15);
        $price->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $price->expects($this->atLeastOnce())->method('getPriceFrom')->willReturn('2016-12-20 01:02:03');
        $price->expects($this->atLeastOnce())->method('getPriceTo')->willReturn('2016-12-21 01:02:03');
        $this->invalidSkuProcessor->expects($this->once())->method('retrieveInvalidSkuList')->willReturn([]);
        $this->storeRepository->expects($this->once())->method('getById');
        $this->validationResult->expects($this->never())->method('addFailedItem');
        $this->validationResult->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([]);
        $this->specialPriceResource->expects($this->once())->method('update')->with($prices);

        $this->model->update($prices);
    }

    /**
     * Test update method with invalid sku.
     *
     * @return void
     */
    public function testUpdateWithInvalidSku()
    {
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\SpecialPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $prices = [1 => $price];
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('sku_1');
        $price->expects($this->atLeastOnce())->method('getPrice')->willReturn(15);
        $price->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $price->expects($this->atLeastOnce())->method('getPriceFrom')->willReturn('2016-12-20 01:02:03');
        $price->expects($this->atLeastOnce())->method('getPriceTo')->willReturn('2016-12-21 01:02:03');
        $this->invalidSkuProcessor->expects($this->once())->method('retrieveInvalidSkuList')->willReturn(['sku_1']);
        $this->storeRepository->expects($this->once())->method('getById');
        $this->validationResult
            ->expects($this->once())
            ->method('addFailedItem')
            ->with(
                1,
                __(
                    'The product that was requested doesn\'t exist. Verify the product and try again. '
                    . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                    [
                        'SKU' => 'sku_1',
                        'storeId' => 1,
                        'priceFrom' => '2016-12-20 01:02:03',
                        'priceTo' => '2016-12-21 01:02:03'
                    ]
                ),
                [
                    'SKU' => 'sku_1',
                    'storeId' => 1,
                    'priceFrom' => '2016-12-20 01:02:03',
                    'priceTo' => '2016-12-21 01:02:03'
                ]
            );
        $this->validationResult->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([1]);
        $this->specialPriceResource->expects($this->once())->method('update')->with([]);

        $this->model->update($prices);
    }

    /**
     * Test update method with price = null.
     *
     * @return void
     */
    public function testUpdateWithoutPrice()
    {
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\SpecialPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $prices = [1 => $price];
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('sku_1');
        $price->expects($this->atLeastOnce())->method('getPrice')->willReturn(null);
        $price->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $price->expects($this->atLeastOnce())->method('getPriceFrom')->willReturn('2016-12-20 01:02:03');
        $price->expects($this->atLeastOnce())->method('getPriceTo')->willReturn('2016-12-21 01:02:03');
        $this->invalidSkuProcessor->expects($this->once())->method('retrieveInvalidSkuList')->willReturn([]);
        $this->storeRepository->expects($this->once())->method('getById');
        $this->validationResult->expects($this->once())
            ->method('addFailedItem')
            ->with(
                1,
                __(
                    'Invalid attribute Price = %price. '
                    . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                    [
                        'price' => null,
                        'SKU' => 'sku_1',
                        'storeId' => 1,
                        'priceFrom' => '2016-12-20 01:02:03',
                        'priceTo' => '2016-12-21 01:02:03'
                    ]
                ),
                [
                    'price' => null,
                    'SKU' => 'sku_1',
                    'storeId' => 1,
                    'priceFrom' => '2016-12-20 01:02:03',
                    'priceTo' => '2016-12-21 01:02:03'
                ]
            );
        $this->validationResult->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([1]);
        $this->specialPriceResource->expects($this->once())->method('update')->with([]);

        $this->model->update($prices);
    }

    /**
     * Test update method with price = null.
     *
     * @return void
     */
    public function testUpdateWithException()
    {
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\SpecialPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $prices = [1 => $price];
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('sku_1');
        $price->expects($this->atLeastOnce())->method('getPrice')->willReturn(15);
        $price->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $price->expects($this->atLeastOnce())->method('getPriceFrom')->willReturn('2016-12-20 01:02:03');
        $price->expects($this->atLeastOnce())->method('getPriceTo')->willReturn('2016-12-21 01:02:03');
        $this->invalidSkuProcessor->expects($this->once())->method('retrieveInvalidSkuList')->willReturn([]);
        $this->storeRepository->expects($this->once())->method('getById')
            ->willThrowException(new NoSuchEntityException());
        $this->validationResult->expects($this->once())
            ->method('addFailedItem')
            ->with(
                1,
                __(
                    'Requested store is not found. '
                    . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                    [
                        'SKU' => 'sku_1',
                        'storeId' => 1,
                        'priceFrom' => '2016-12-20 01:02:03',
                        'priceTo' => '2016-12-21 01:02:03'
                    ]
                ),
                [
                    'SKU' => 'sku_1',
                    'storeId' => 1,
                    'priceFrom' => '2016-12-20 01:02:03',
                    'priceTo' => '2016-12-21 01:02:03'
                ]
            );
        $this->validationResult->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([1]);
        $this->specialPriceResource->expects($this->once())->method('update')->with([]);

        $this->model->update($prices);
    }

    /**
     * Test update method with incorrect price_from field.
     *
     * @return void
     */
    public function testUpdateWithIncorrectPriceFrom()
    {
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\SpecialPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $prices = [1 => $price];
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('sku_1');
        $price->expects($this->atLeastOnce())->method('getPrice')->willReturn(15);
        $price->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $price->expects($this->atLeastOnce())->method('getPriceFrom')->willReturn('incorrect');
        $price->expects($this->atLeastOnce())->method('getPriceTo')->willReturn('2016-12-21 01:02:03');
        $this->invalidSkuProcessor->expects($this->once())->method('retrieveInvalidSkuList')->willReturn([]);
        $this->storeRepository->expects($this->once())->method('getById');
        $this->validationResult->expects($this->once())
            ->method('addFailedItem')
            ->with(
                1,
                __(
                    'Invalid attribute %label = %priceTo. '
                    . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                    [
                        'label' => 'Price From',
                        'SKU' => 'sku_1',
                        'storeId' => 1,
                        'priceFrom' => 'incorrect',
                        'priceTo' => '2016-12-21 01:02:03'
                    ]
                ),
                [
                    'label' => 'Price From',
                    'SKU' => 'sku_1',
                    'storeId' => 1,
                    'priceFrom' => 'incorrect',
                    'priceTo' => '2016-12-21 01:02:03'
                ]
            );
        $this->validationResult->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([1]);
        $this->specialPriceResource->expects($this->once())->method('update')->with([]);

        $this->model->update($prices);
    }

    /**
     * Test update method with incorrect price_to field.
     *
     * @return void
     */
    public function testUpdateWithIncorrectPriceTo()
    {
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\SpecialPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $prices = [1 => $price];
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('sku_1');
        $price->expects($this->atLeastOnce())->method('getPrice')->willReturn(15);
        $price->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $price->expects($this->atLeastOnce())->method('getPriceFrom')->willReturn('2016-12-21 01:02:03');
        $price->expects($this->atLeastOnce())->method('getPriceTo')->willReturn('incorrect');
        $this->invalidSkuProcessor->expects($this->once())->method('retrieveInvalidSkuList')->willReturn([]);
        $this->storeRepository->expects($this->once())->method('getById');
        $this->validationResult->expects($this->once())
            ->method('addFailedItem')
            ->with(
                1,
                __(
                    'Invalid attribute %label = %priceTo. '
                    . 'Row ID: SKU = %SKU, Store ID: %storeId, Price From: %priceFrom, Price To: %priceTo.',
                    [
                        'label' => 'Price To',
                        'SKU' => 'sku_1',
                        'storeId' => 1,
                        'priceFrom' => '2016-12-21 01:02:03',
                        'priceTo' => 'incorrect'
                    ]
                ),
                [
                    'label' => 'Price To',
                    'SKU' => 'sku_1',
                    'storeId' => 1,
                    'priceFrom' => '2016-12-21 01:02:03',
                    'priceTo' => 'incorrect'
                ]
            );
        $this->validationResult->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([1]);
        $this->specialPriceResource->expects($this->once())->method('update')->with([]);

        $this->model->update($prices);
    }
}

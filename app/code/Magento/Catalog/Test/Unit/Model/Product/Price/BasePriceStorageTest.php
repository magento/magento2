<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

/**
 * Class BasePriceStorageTest.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasePriceStorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Price\PricePersistenceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pricePersistenceFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Price\PricePersistence|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pricePersistence;

    /**
     * @var \Magento\Catalog\Api\Data\BasePriceInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $basePriceInterfaceFactory;

    /**
     * @var \Magento\Catalog\Api\Data\BasePriceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $basePriceInterface;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productIdLocator;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $storeRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor
     *      |\PHPUnit\Framework\MockObject\MockObject
     */
    private $invalidSkuProcessor;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result|\PHPUnit\Framework\MockObject\MockObject
     */
    private $validationResult;

    /**
     * @var \Magento\Catalog\Model\Product\Price\BasePriceStorage
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->pricePersistenceFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Price\PricePersistenceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->pricePersistence = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\PricePersistence::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->basePriceInterfaceFactory = $this->getMockBuilder(
            \Magento\Catalog\Api\Data\BasePriceInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->basePriceInterface = $this->getMockBuilder(\Magento\Catalog\Api\Data\BasePriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productIdLocator = $this->getMockBuilder(\Magento\Catalog\Model\ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeRepository = $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->invalidSkuProcessor = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validationResult = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\BasePriceStorage::class,
            [
                'pricePersistenceFactory' => $this->pricePersistenceFactory,
                'basePriceInterfaceFactory' => $this->basePriceInterfaceFactory,
                'productIdLocator' => $this->productIdLocator,
                'storeRepository' => $this->storeRepository,
                'invalidSkuProcessor' => $this->invalidSkuProcessor,
                'validationResult' => $this->validationResult,
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
        $skus = ['sku_1', 'sku_2', 'sku_3'];
        $validSkus = ['sku_1', 'sku_2'];
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
        $this->invalidSkuProcessor->expects($this->once())
            ->method('filterSkuList')
            ->with($skus, ['simple', 'virtual', 'bundle', 'downloadable'], 1)
            ->willReturn($validSkus);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'price'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->once())->method('get')->with($validSkus)->willReturn($rawPrices);
        $this->basePriceInterfaceFactory
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->basePriceInterface);
        $this->pricePersistence->expects($this->atLeastOnce())->method('getEntityLinkField')->willReturn('row_id');
        $this->pricePersistence
            ->expects($this->atLeastOnce())
            ->method('retrieveSkuById')
            ->willReturnOnConsecutiveCalls('sku_1', 'sku_2');
        $this->basePriceInterface
            ->expects($this->atLeastOnce())
            ->method('setSku')
            ->withConsecutive(['sku_1'], ['sku_2'])
            ->willReturnSelf();
        $this->basePriceInterface
            ->expects($this->atLeastOnce())
            ->method('setPrice')
            ->withConsecutive([15], [35])
            ->willReturnSelf();
        $this->basePriceInterface
            ->expects($this->atLeastOnce())
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
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' =>
                [
                    1 => [
                        $this->basePriceInterface
                    ]
                ]
        ];
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->invalidSkuProcessor->expects($this->once())
            ->method('retrieveInvalidSkuList')
            ->with([1 => $sku], ['simple', 'virtual', 'bundle', 'downloadable'], 1)
            ->willReturn([]);
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getPrice')->willReturn(15);
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $this->validationResult->expects($this->once())->method('getFailedRowIds')->willReturn([]);
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')->with([$sku])
            ->willReturn($idsBySku);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'price'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->atLeastOnce())->method('getEntityLinkField')->willReturn('row_id');
        $this->storeRepository->expects($this->once())->method('getById')->with(1)->willReturn($store);
        $formattedPrices = [
            [
                'store_id' => 1,
                'row_id' => 1,
                'value' => 15
            ]
        ];
        $this->pricePersistence->expects($this->once())->method('update')->with($formattedPrices);
        $this->validationResult->expects($this->any())->method('getFailedItems')->willReturn([]);
        $this->assertEquals([], $this->model->update([1 => $this->basePriceInterface]));
    }

    /**
     * Test update method without SKU and with negative price.
     *
     * @return void
     */
    public function testUpdateWithoutSkuAndWithNegativePrice()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getSku')->willReturn(null);
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getPrice')->willReturn(-10);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'price'])
            ->willReturn($this->pricePersistence);
        $this->invalidSkuProcessor->expects($this->once())
            ->method('retrieveInvalidSkuList')
            ->with([null], ['simple', 'virtual', 'bundle', 'downloadable'], 1)
            ->willReturn([]);
        $priceUpdateResult = $this->getMockBuilder(\Magento\Catalog\Api\Data\PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validationResult->expects($this->atLeastOnce())
            ->method('addFailedItem')
            ->withConsecutive(
                [
                    0,
                    __(
                        'Invalid attribute %fieldName = %fieldValue.',
                        ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                    ),
                    ['fieldName' => 'SKU', 'fieldValue' => null]
                ],
                [
                    0,
                    __(
                        'Invalid attribute %fieldName = %fieldValue.',
                        ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                    ),
                    ['fieldName' => 'Price', 'fieldValue' => -10]
                ],
                [
                    0,
                    __(
                        'Requested store is not found. Row ID: SKU = %SKU, Store ID: %storeId.',
                        ['SKU' => null, 'storeId' => 10]
                    ),
                    ['SKU' => null, 'storeId' => 10]
                ]
            );
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getStoreId')->willReturn(10);
        $this->storeRepository->expects($this->once())->method('getById')->with(10)->willThrowException($exception);
        $this->validationResult->expects($this->once())->method('getFailedRowIds')->willReturn([0 => 0]);
        $this->pricePersistence->expects($this->once())->method('update')->with([]);
        $this->validationResult->expects($this->once())->method('getFailedItems')->willReturn([$priceUpdateResult]);

        $this->assertEquals(
            [$priceUpdateResult],
            $this->model->update([$this->basePriceInterface])
        );
    }
}

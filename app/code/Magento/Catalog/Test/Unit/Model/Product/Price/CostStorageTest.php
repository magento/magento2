<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

use Magento\Catalog\Api\Data\CostInterface;
use Magento\Catalog\Api\Data\CostInterfaceFactory;
use Magento\Catalog\Api\Data\PriceUpdateResultInterface;
use Magento\Catalog\Model\Product\Price\CostStorage;
use Magento\Catalog\Model\Product\Price\PricePersistence;
use Magento\Catalog\Model\Product\Price\PricePersistenceFactory;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Price\Validation\Result;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CostStorageTest extends TestCase
{
    /**
     * @var PricePersistenceFactory|MockObject
     */
    private $pricePersistenceFactory;

    /**
     * @var PricePersistence|MockObject
     */
    private $pricePersistence;

    /**
     * @var CostInterfaceFactory|MockObject
     */
    private $costInterfaceFactory;

    /**
     * @var CostInterface|MockObject
     */
    private $costInterface;

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
     * @var CostStorage
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
            PricePersistenceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->pricePersistence = $this->getMockBuilder(PricePersistence::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->costInterfaceFactory = $this->getMockBuilder(CostInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->costInterface = $this->getMockBuilder(CostInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productIdLocator = $this->getMockBuilder(ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeRepository = $this->getMockBuilder(StoreRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validationResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invalidSkuProcessor = $this
            ->getMockBuilder(InvalidSkuProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            CostStorage::class,
            [
                'pricePersistenceFactory' => $this->pricePersistenceFactory,
                'costInterfaceFactory' => $this->costInterfaceFactory,
                'productIdLocator' => $this->productIdLocator,
                'storeRepository' => $this->storeRepository,
                'validationResult' => $this->validationResult,
                'invalidSkuProcessor' => $this->invalidSkuProcessor,
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
        $this->invalidSkuProcessor
            ->expects($this->once())
            ->method('filterSkuList')
            ->with($skus, ['simple', 'virtual', 'downloadable'])
            ->willReturn($skus);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'cost'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->once())->method('get')->with($skus)->willReturn($rawPrices);
        $this->costInterfaceFactory
            ->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($this->costInterface);
        $this->pricePersistence
            ->expects($this->atLeastOnce())
            ->method('retrieveSkuById')
            ->willReturnOnConsecutiveCalls('sku_1', 'sku_2');
        $this->pricePersistence->expects($this->atLeastOnce())->method('getEntityLinkField')->willReturn('row_id');
        $this->costInterface
            ->expects($this->atLeastOnce())
            ->method('setSku')
            ->withConsecutive(['sku_1'], ['sku_2'])
            ->willReturnSelf();
        $this->costInterface
            ->expects($this->atLeastOnce())
            ->method('setCost')
            ->withConsecutive([15], [35])
            ->willReturnSelf();
        $this->costInterface
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
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [
                1 => Type::TYPE_VIRTUAL
            ]
        ];
        $this->costInterface->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->invalidSkuProcessor
            ->expects($this->once())
            ->method('retrieveInvalidSkuList')
            ->willReturn([]);
        $this->costInterface->expects($this->atLeastOnce())->method('getCost')->willReturn(15);
        $this->costInterface->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $this->validationResult
            ->expects($this->once())
            ->method('getFailedRowIds')
            ->willReturn([]);
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')->with([$sku])
            ->willReturn($idsBySku);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'cost'])
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
        $this->validationResult
            ->expects($this->once())
            ->method('getFailedItems')
            ->willReturn([]);

        $this->assertEmpty($this->model->update([$this->costInterface]));
    }

    /**
     * Test update method with negative cost and without SKU.
     *
     * @return void
     */
    public function testUpdateWithNegativeCostAndWithoutSku()
    {
        $exception = new NoSuchEntityException();
        $this->costInterface->expects($this->atLeastOnce())->method('getSku')->willReturn(null);
        $this->costInterface->expects($this->atLeastOnce())->method('getCost')->willReturn(-15);
        $this->costInterface->expects($this->atLeastOnce())->method('getStoreId')->willReturn(10);
        $this->validationResult->expects($this->once())->method('getFailedRowIds')->willReturn([0 => 0]);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'cost'])
            ->willReturn($this->pricePersistence);
        $priceUpdateResult = $this->getMockBuilder(PriceUpdateResultInterface::class)
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
                        'Invalid attribute Cost = %cost. Row ID: SKU = %SKU, Store ID: %storeId.',
                        ['cost' => -15, 'SKU' => null, 'storeId' => 10]
                    ),
                    ['cost' => -15, 'SKU' => null, 'storeId' => 10]
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
        $this->storeRepository->expects($this->once())->method('getById')->with(10)->willThrowException($exception);
        $this->invalidSkuProcessor
            ->expects($this->once())
            ->method('retrieveInvalidSkuList')
            ->willReturn([]);
        $this->pricePersistence->expects($this->once())->method('update')->with([]);
        $this->validationResult->expects($this->once())->method('getFailedItems')->willReturn([$priceUpdateResult]);

        $this->assertEquals(
            [$priceUpdateResult],
            $this->model->update([$this->costInterface])
        );
    }

    /**
     * Test delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $skus = ['sku_1', 'sku_2'];
        $this->invalidSkuProcessor
            ->expects($this->once())
            ->method('filterSkuList')
            ->with($skus, ['simple', 'virtual', 'downloadable'])
            ->willReturn($skus);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'cost'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->once())->method('delete')->with($skus);

        $this->model->delete($skus);
    }
}

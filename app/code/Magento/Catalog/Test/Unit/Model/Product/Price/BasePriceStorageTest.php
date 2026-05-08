<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\Data\BasePriceInterface;
use Magento\Catalog\Api\Data\BasePriceInterfaceFactory;
use Magento\Catalog\Api\Data\PriceUpdateResultInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Price\BasePriceStorage;
use Magento\Catalog\Model\Product\Price\PricePersistence;
use Magento\Catalog\Model\Product\Price\PricePersistenceFactory;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Price\Validation\Result;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Framework\Exception\InputException;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BasePriceStorageTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var PricePersistenceFactory|MockObject
     */
    private $pricePersistenceFactory;

    /**
     * @var PricePersistence|MockObject
     */
    private $pricePersistence;

    /**
     * @var BasePriceInterfaceFactory|MockObject
     */
    private $basePriceInterfaceFactory;

    /**
     * @var BasePriceInterface|MockObject
     */
    private $basePriceInterface;

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
     * @var BasePriceStorage
     */
    private $model;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->pricePersistenceFactory = $this->createPartialMock(
            PricePersistenceFactory::class,
            ['create']
        );
        $this->pricePersistence = $this->createMock(PricePersistence::class);
        $this->basePriceInterfaceFactory = $this->createPartialMock(
            BasePriceInterfaceFactory::class,
            ['create']
        );
        $this->basePriceInterface = $this->createMock(BasePriceInterface::class);
        $this->productIdLocator = $this->createMock(ProductIdLocatorInterface::class);
        $this->storeRepository = $this->createMock(StoreRepositoryInterface::class);
        $this->invalidSkuProcessor = $this->createMock(InvalidSkuProcessor::class);
        $this->validationResult = $this->createMock(Result::class);
        $this->productAttributeRepository = $this->createMock(ProductAttributeRepositoryInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            BasePriceStorage::class,
            [
                'pricePersistenceFactory' => $this->pricePersistenceFactory,
                'basePriceInterfaceFactory' => $this->basePriceInterfaceFactory,
                'productIdLocator' => $this->productIdLocator,
                'storeRepository' => $this->storeRepository,
                'invalidSkuProcessor' => $this->invalidSkuProcessor,
                'validationResult' => $this->validationResult,
                'allowedProductTypes' => ['simple', 'virtual', 'bundle', 'downloadable'],
                'productAttributeRepository' => $this->productAttributeRepository
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
            ->willReturnCallback(function ($arg) {
                if ($arg == 'sku_1' || $arg == 'sku_2') {
                    return $this->basePriceInterface;
                }
            });
        $this->basePriceInterface
            ->expects($this->atLeastOnce())
            ->method('setPrice')
            ->willReturnCallback(function ($arg) {
                if ($arg == 15 || $arg == 35) {
                    return $this->basePriceInterface;
                }
            });
        $this->basePriceInterface
            ->expects($this->atLeastOnce())
            ->method('setStoreId')
            ->willReturnCallback(function ($arg) {
                if ($arg == 1 || $arg == 1) {
                    return $this->basePriceInterface;
                }
            });

        $this->model->get($skus);
    }

    /**
     * Test update method.
     *
     * @param bool $isScopeWebsite
     * @param bool $isScopeGlobal
     * @param array $formattedPrices
     * @return void
     */
    #[DataProvider('updateProvider')]
    public function testUpdate(bool $isScopeWebsite, bool $isScopeGlobal, array $formattedPrices)
    {
        /** @var WebsiteInterface $website */
        $website = $this->createPartialMockWithReflection(Website::class, ['setStoreIds', 'getStoreIds']);
        $storeIds = null;
        $website->method('setStoreIds')->willReturnCallback(
            function ($ids) use (&$storeIds, $website) {
                $storeIds = $ids;
                return $website;
            }
        );
        $website->method('getStoreIds')->willReturnCallback(
            function () use (&$storeIds) {
                return $storeIds;
            }
        );
        $website->setStoreIds([1 => 1, 2 => 2]);
        /** @var StoreInterface $store */
        $store = $this->createPartialMockWithReflection(Store::class, ['setWebsite', 'getWebsite']);
        $websiteObj = null;
        $store->method('setWebsite')->willReturnCallback(
            function ($web) use (&$websiteObj, $store) {
                $websiteObj = $web;
                return $store;
            }
        );
        $store->method('getWebsite')->willReturnCallback(
            function () use (&$websiteObj) {
                return $websiteObj;
            }
        );
        $store->setWebsite($website);
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [
                1 => [
                    $this->basePriceInterface
                ]
            ]
        ];
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->invalidSkuProcessor->expects($this->any())
            ->method('retrieveInvalidSkuList')
            ->with([1 => $sku], ['simple', 'virtual', 'bundle', 'downloadable'], 1)
            ->willReturn([]);
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getPrice')->willReturn(15);
        $this->basePriceInterface->expects($this->atLeastOnce())->method('getStoreId')->willReturn(1);
        $this->validationResult->method('getFailedRowIds')->willReturn([]);
        $this->productIdLocator
            ->expects($this->any())
            ->method('retrieveProductIdsBySkus')->with([$sku])
            ->willReturn($idsBySku);
        $this->pricePersistenceFactory
            ->expects($this->once())
            ->method('create')
            ->with(['attributeCode' => 'price'])
            ->willReturn($this->pricePersistence);
        $this->pricePersistence->expects($this->atLeastOnce())->method('getEntityLinkField')->willReturn('row_id');
        $this->storeRepository->expects($this->any())->method('getById')->with(1)->willReturn($store);
        $this->pricePersistence->expects($this->any())->method('update')->with($formattedPrices);
        $this->validationResult->method('getFailedItems')->willReturn([]);
        /** @var ProductAttributeInterface $attribute */
        $attribute = $this->createPartialMockWithReflection(
            AbstractAttribute::class,
            ['setIsScopeWebsite', 'isScopeWebsite', 'setIsScopeGlobal', 'isScopeGlobal', '_construct']
        );
        $attribute->method('setIsScopeWebsite')->willReturnSelf();
        $attribute->method('isScopeWebsite')->willReturn($isScopeWebsite);
        $attribute->method('setIsScopeGlobal')->willReturnSelf();
        $attribute->method('isScopeGlobal')->willReturn($isScopeGlobal);
        $attribute->setIsScopeWebsite($isScopeWebsite);
        $attribute->setIsScopeGlobal($isScopeGlobal);
        $this->productAttributeRepository
            ->method('get')
            ->willReturn($attribute);

        $this->assertEquals([], $this->model->update([1 => $this->basePriceInterface]));
    }

    /**
     * Test update method without SKU and with negative price.
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testUpdateWithoutSkuAndWithNegativePrice()
    {
        $exception = new NoSuchEntityException();
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
        $priceUpdateResult = $this->createMock(PriceUpdateResultInterface::class);
        $this->validationResult->expects($this->atLeastOnce())
            ->method('addFailedItem')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 === 0 &&
                    $arg2 == __(
                        'Invalid attribute %fieldName = %fieldValue.',
                        ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                    ) &&
                    $arg3 == ['fieldName' => 'SKU', 'fieldValue' => null]) {
                    return $this->validationResult;
                } elseif ($arg1 === 0 &&
                    $arg2 == __(
                        'Invalid attribute %fieldName = %fieldValue.',
                        ['fieldName' => '%fieldName', 'fieldValue' => '%fieldValue']
                    ) &&
                    $arg3 == ['fieldName' => 'Price', 'fieldValue' => -10]) {
                    return $this->validationResult;
                } elseif ($arg1 === 0 &&
                    $arg2 == __(
                        'Requested store is not found. Row ID: SKU = %SKU, Store ID: %storeId.',
                        ['SKU' => null, 'storeId' => 10]
                    ) &&
                    $arg3 == ['SKU' => null, 'storeId' => 10]) {
                    return $this->validationResult;
                }
            });
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

    /**
     * Data provider for update.
     *
     * @return array
     */
    public static function updateProvider(): array
    {
        return
            [
                [
                    'isScopeWebsite' => false,
                    'isScopeGlobal' => false,
                    'formattedPrices' => [
                        [
                            'store_id' => 1,
                            'row_id' => 1,
                            'value' => 15
                        ]
                    ]
                ],
                [
                    'isScopeWebsite' => true,
                    'isScopeGlobal' => false,
                    'formattedPrices' => [
                        [
                            'store_id' => 1,
                            'row_id' => 1,
                            'value' => 15
                        ],
                        [
                            'store_id' => 2,
                            'row_id' => 1,
                            'value' => 15
                        ]
                    ]
                ],
                [
                    'isScopeWebsite' => false,
                    'isScopeGlobal' => true,
                    'formattedPrices' => [
                        [
                            'store_id' => 0,
                            'row_id' => 1,
                            'value' => 15
                        ]
                    ]
                ]
            ];
    }

    /**
     * Test update method with null input - should throw InputException
     */
    public function testUpdateWithNullInput(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Invalid input data format. Expected an array of prices.');

        $this->model->update(null);
    }

    /**
     * Test update method with non-array input - should throw InputException
     */
    public function testUpdateWithInvalidInput(): void
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Invalid input data format. Expected an array of prices.');

        $this->model->update('invalid_string');
    }
}

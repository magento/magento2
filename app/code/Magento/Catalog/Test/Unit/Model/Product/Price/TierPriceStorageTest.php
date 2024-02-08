<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexerProcessor;
use Magento\Catalog\Model\Product\Price\TierPriceFactory;
use Magento\Catalog\Model\Product\Price\TierPricePersistence;
use Magento\Catalog\Model\Product\Price\TierPriceStorage;
use Magento\Catalog\Model\Product\Price\Validation\Result as PriceValidationResult;
use Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Customer\Model\ResourceModel\Group\GetCustomerGroupCodesByIds;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TierPriceStorageTest extends TestCase
{
    /**
     * @var TierPricePersistence|MockObject
     */
    private $tierPricePersistence;

    /**
     * @var TierPriceValidator|MockObject
     */
    private $tierPriceValidator;

    /**
     * @var TierPriceFactory|MockObject
     */
    private $tierPriceFactory;

    /**
     * @var PriceIndexerProcessor|MockObject
     */
    private $priceIndexProcessor;

    /**
     * @var ProductIdLocatorInterface|MockObject
     */
    private $productIdLocator;

    /**
     * @var TierPriceStorage
     */
    private $tierPriceStorage;

    /**
     * @var GetCustomerGroupCodesByIds|MockObject
     */
    private $getCustomerGroupCodesByIds;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->tierPricePersistence = $this->createMock(TierPricePersistence::class);
        $this->tierPricePersistence->method('getEntityLinkField')
            ->willReturn('entity_id');
        $this->tierPriceValidator = $this->createMock(TierPriceValidator::class);
        $this->tierPriceFactory = $this->createMock(TierPriceFactory::class);
        $this->priceIndexProcessor = $this->createMock(PriceIndexerProcessor::class);
        $this->productIdLocator = $this->getMockForAbstractClass(ProductIdLocatorInterface::class);
        $this->getCustomerGroupCodesByIds = $this->createMock(GetCustomerGroupCodesByIds::class);

        $this->tierPriceStorage = new TierPriceStorage(
            $this->tierPricePersistence,
            $this->tierPriceValidator,
            $this->tierPriceFactory,
            $this->priceIndexProcessor,
            $this->productIdLocator,
            $this->getCustomerGroupCodesByIds
        );
    }

    /**
     * Test get method.
     *
     * @return void
     */
    public function testGet()
    {
        $skus = ['simple', 'virtual'];
        $rawPricesData = [
            [
                'value_id' => 1,
                'entity_id' => 2,
                'all_groups' => 1,
                'customer_group_id' => 0,
                'qty' => 2.0000,
                'value' => 2.0000,
                'percentage_value' => null,
                'website_id' => 0
            ],
            [
                'value_id' => 2,
                'entity_id' => 3,
                'all_groups' => 0,
                'customer_group_id' => 1,
                'qty' => 3.0000,
                'value' => 3.0000,
                'percentage_value' => null,
                'website_id' => 0
            ],
            [
                'value_id' => 3,
                'entity_id' => 3,
                'all_groups' => 0,
                'customer_group_id' => 2,
                'qty' => 3.0000,
                'value' => 3.0000,
                'percentage_value' => null,
                'website_id' => 0
            ]
        ];
        $this->tierPriceValidator
            ->expects($this->once())
            ->method('validateSkus')
            ->with($skus)
            ->willReturn($skus);
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->with(['simple', 'virtual'])
            ->willReturn(['simple' => ['2' => 'simple'], 'virtual' => ['3' => 'virtual']]);
        $this->tierPricePersistence->expects($this->once())
            ->method('get')
            ->willReturn($rawPricesData);
        $this->getCustomerGroupCodesByIds->expects($this->once())
            ->method('execute')
            ->with([1, 2])
            ->willReturn([1 => 'General', 2 => 'Wholesale']);
        $price = $this->getMockBuilder(TierPriceInterface::class)
            ->getMockForAbstractClass();
        $this->tierPriceFactory
            ->expects($this->exactly(3))
            ->method('create')
            ->withConsecutive(
                [$rawPricesData[0], 'simple'],
                [$rawPricesData[1] + ['customer_group_code' => 'General'], 'virtual'],
                [$rawPricesData[2] + ['customer_group_code' => 'Wholesale'], 'virtual'],
            )
            ->willReturn($price);
        $prices = $this->tierPriceStorage->get($skus);
        $this->assertNotEmpty($prices);
        $this->assertCount(3, $prices);
    }

    /**
     * Test get method without tierprices.
     *
     * @return void
     */
    public function testGetWithoutTierPrices()
    {
        $skus = ['simple', 'virtual'];
        $this->tierPriceValidator
            ->expects($this->once())
            ->method('validateSkus')
            ->with($skus)
            ->willReturn($skus);
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->with(['simple', 'virtual'])
            ->willReturn(['simple' => ['2' => 'simple'], 'virtual' => ['3' => 'virtual']]);
        $this->tierPricePersistence->expects($this->once())->method('get')->willReturn([]);
        $this->tierPricePersistence->expects($this->never())->method('getEntityLinkField');
        $this->tierPriceFactory->expects($this->never())->method('create');
        $prices = $this->tierPriceStorage->get($skus);
        $this->assertEmpty($prices);
    }

    /**
     * Test update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $price = $this->getMockForAbstractClass(TierPriceInterface::class);
        $result = $this->createMock(PriceValidationResult::class);
        $result->expects($this->once())
            ->method('getFailedRowIds')
            ->willReturn([]);
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->willReturn(['simple' => ['2' => 'simple'], 'virtual' => ['3' => 'virtual']]);
        $this->tierPriceValidator->expects($this->once())
            ->method('retrieveValidationResult')
            ->willReturn($result);
        $this->tierPriceFactory->expects($this->once())
            ->method('createSkeleton')
            ->willReturn(
                [
                    'entity_id' => 2,
                    'all_groups' => 1,
                    'customer_group_id' => 0,
                    'qty' => 2,
                    'value' => 3,
                    'percentage_value' => null,
                    'website_id' => 0
                ]
            );
        $this->tierPricePersistence->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    [
                        'value_id' => 1,
                        'entity_id' => 2,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 2.0000,
                        'value' => 2.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ]
                ]
            );
        $this->tierPricePersistence->expects($this->once())
            ->method('update');
        $this->priceIndexProcessor->expects($this->once())
            ->method('reindexList')
            ->with([2, 3]);
        $price->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn('simple');

        $this->assertEmpty($this->tierPriceStorage->update([$price]));
    }

    /**
     * Test replace method.
     *
     * @return void
     */
    public function testReplace()
    {
        $price = $this->getMockForAbstractClass(TierPriceInterface::class);
        $price->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn('virtual');
        $result = $this->createMock(PriceValidationResult::class);
        $result->expects($this->once())
            ->method('getFailedRowIds')
            ->willReturn([]);
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->willReturn(['simple' => ['2' => 'simple'], 'virtual' => ['3' => 'virtual']]);

        $this->tierPriceValidator
            ->expects($this->once())
            ->method('retrieveValidationResult')
            ->willReturn($result);
        $this->tierPriceFactory->expects($this->once())
            ->method('createSkeleton')
            ->willReturn(
                [
                    'entity_id' => 3,
                    'all_groups' => 1,
                    'customer_group_id' => 0,
                    'qty' => 3,
                    'value' => 7,
                    'percentage_value' => null,
                    'website_id' => 0
                ]
            );
        $this->tierPricePersistence->expects($this->once())
            ->method('replace');
        $this->priceIndexProcessor->expects($this->once())
            ->method('reindexList')
            ->with([2, 3]);

        $this->assertEmpty($this->tierPriceStorage->replace([$price]));
    }

    /**
     * Test delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $price = $this->getMockForAbstractClass(TierPriceInterface::class);
        $price->expects($this->atLeastOnce())
            ->method('getSku')
            ->willReturn('simple');
        $result = $this->createMock(PriceValidationResult::class);
        $result->expects($this->once())
            ->method('getFailedRowIds')
            ->willReturn([]);
        $this->tierPriceValidator->expects($this->once())
            ->method('retrieveValidationResult')
            ->willReturn($result);
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->willReturn(['simple' => ['2' => 'simple']]);
        $this->tierPricePersistence->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    [
                        'value_id' => 7,
                        'entity_id' => 7,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 5.0000,
                        'value' => 6.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ]
                ]
            );
        $this->tierPriceFactory->expects($this->once())
            ->method('createSkeleton')->willReturn(
                [
                    'entity_id' => 3,
                    'all_groups' => 1,
                    'customer_group_id' => 0,
                    'qty' => 3,
                    'value' => 7,
                    'percentage_value' => null,
                    'website_id' => 0
                ]
            );
        $this->tierPricePersistence->expects($this->once())
            ->method('delete');
        $this->priceIndexProcessor->expects($this->once())
            ->method('reindexList')
            ->with([2]);

        $this->assertEmpty($this->tierPriceStorage->delete([$price]));
    }
}

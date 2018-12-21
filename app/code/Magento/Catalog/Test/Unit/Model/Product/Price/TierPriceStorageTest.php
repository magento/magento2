<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

/**
 * TierPriceStorage test.
 */
class TierPriceStorageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPricePersistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPricePersistence;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceValidator;

    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPriceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceIndexer;

    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productIdLocator;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typeList;

    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPriceStorage
     */
    private $tierPriceStorage;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->tierPricePersistence = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Price\TierPricePersistence::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->tierPricePersistence->expects($this->any())
            ->method('getEntityLinkField')
            ->willReturn('row_id');
        $this->tierPriceValidator = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->tierPriceFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Price\TierPriceFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceIndexer = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Product\Price::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productIdLocator = $this->getMockBuilder(\Magento\Catalog\Model\ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->config = $this->getMockBuilder(\Magento\PageCache\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typeList = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->tierPriceStorage = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\TierPriceStorage::class,
            [
                'tierPricePersistence' => $this->tierPricePersistence,
                'tierPriceValidator' => $this->tierPriceValidator,
                'tierPriceFactory' => $this->tierPriceFactory,
                'priceIndexer' => $this->priceIndexer,
                'productIdLocator' => $this->productIdLocator,
                'config' => $this->config,
                'typeList' => $this->typeList,
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
        $this->tierPricePersistence->expects($this->once())
            ->method('get')
            ->willReturn(
                [
                    [
                        'value_id' => 1,
                        'row_id' => 2,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 2.0000,
                        'value' => 2.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ],
                    [
                        'value_id' => 2,
                        'row_id' => 3,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 3.0000,
                        'value' => 3.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ]
                ]
            );
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)->getMockForAbstractClass();
        $this->tierPriceFactory->expects($this->atLeastOnce())->method('create')->willReturn($price);
        $prices = $this->tierPriceStorage->get($skus);
        $this->assertNotEmpty($prices);
        $this->assertEquals(2, count($prices));
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
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)->getMockForAbstractClass();
        $result = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([]);
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->willReturn(['simple' => ['2' => 'simple'], 'virtual' => ['3' => 'virtual']]);
        $this->tierPriceValidator
            ->expects($this->atLeastOnce())
            ->method('retrieveValidationResult')
            ->willReturn($result);
        $this->tierPriceFactory->expects($this->atLeastOnce())->method('createSkeleton')->willReturn(
            [
                'row_id' => 2,
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
                        'row_id' => 2,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 2.0000,
                        'value' => 2.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ]
                ]
            );
        $this->tierPricePersistence->expects($this->atLeastOnce())->method('update');
        $this->priceIndexer->expects($this->atLeastOnce())->method('execute');
        $this->config->expects($this->atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->typeList->expects($this->atLeastOnce())->method('invalidate');
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('simple');
        $this->assertEmpty($this->tierPriceStorage->update([$price]));
    }

    /**
     * Test replace method.
     *
     * @return void
     */
    public function testReplace()
    {
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)->getMockForAbstractClass();
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('virtual');
        $result = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([]);
        $this->productIdLocator->expects($this->atLeastOnce())
            ->method('retrieveProductIdsBySkus')
            ->willReturn(['simple' => ['2' => 'simple'], 'virtual' => ['3' => 'virtual']]);

        $this->tierPriceValidator
            ->expects($this->atLeastOnce())
            ->method('retrieveValidationResult')
            ->willReturn($result);
        $this->tierPriceFactory->expects($this->atLeastOnce())->method('createSkeleton')->willReturn(
            [
                'row_id' => 3,
                'all_groups' => 1,
                'customer_group_id' => 0,
                'qty' => 3,
                'value' => 7,
                'percentage_value' => null,
                'website_id' => 0
            ]
        );
        $this->tierPricePersistence->expects($this->atLeastOnce())->method('replace');
        $this->priceIndexer->expects($this->atLeastOnce())->method('execute');
        $this->config->expects($this->atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->typeList->expects($this->atLeastOnce())->method('invalidate');
        $this->assertEmpty($this->tierPriceStorage->replace([$price]));
    }

    /**
     * Test delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $price = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)->getMockForAbstractClass();
        $price->expects($this->atLeastOnce())->method('getSku')->willReturn('simple');
        $result = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->atLeastOnce())->method('getFailedRowIds')->willReturn([]);
        $this->tierPriceValidator->expects($this->atLeastOnce())
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
                        'row_id' => 7,
                        'all_groups' => 1,
                        'customer_group_id' => 0,
                        'qty' => 5.0000,
                        'value' => 6.0000,
                        'percentage_value' => null,
                        'website_id' => 0
                    ]
                ]
            );
        $this->tierPriceFactory->expects($this->atLeastOnce())->method('createSkeleton')->willReturn(
            [
                'row_id' => 3,
                'all_groups' => 1,
                'customer_group_id' => 0,
                'qty' => 3,
                'value' => 7,
                'percentage_value' => null,
                'website_id' => 0
            ]
        );
        $this->tierPricePersistence->expects($this->atLeastOnce())->method('delete');
        $this->priceIndexer->expects($this->atLeastOnce())->method('execute');
        $this->config->expects($this->atLeastOnce())->method('isEnabled')->willReturn(true);
        $this->typeList->expects($this->atLeastOnce())->method('invalidate');
        $this->assertEmpty($this->tierPriceStorage->delete([$price]));
    }
}

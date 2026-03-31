<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\Collection;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Collection\SalableProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SalableProcessorTest extends TestCase
{
    private const STOCK_FLAG = 'has_stock_status_filter';

    /** @var ObjectManager */
    private $objectManager;

    /** @var SalableProcessor */
    protected $model;

    /** @var MockObject */
    protected $stockStatusFactory;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->stockStatusFactory = $this->createPartialMock(
            StatusFactory::class,
            ['create']
        );

        $this->model = $this->objectManager->getObject(
            SalableProcessor::class,
            [
                'stockStatusFactory' => $this->stockStatusFactory,
            ]
        );
    }

    public function testProcess()
    {
        $productCollection = $this->createPartialMock(Collection::class, ['addAttributeToFilter']);

        $productCollection->expects($this->once())
            ->method('addAttributeToFilter')
            ->with(ProductInterface::STATUS, Status::STATUS_ENABLED)->willReturnSelf();

        $stockStatusResource = $this->createPartialMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Status::class,
            ['addStockDataToCollection']
        );
        $stockStatusResource->expects($this->once())
            ->method('addStockDataToCollection')
            ->with($productCollection, true)->willReturnSelf();

        $this->stockStatusFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($stockStatusResource);

        $this->model->process($productCollection);

        $this->assertTrue($productCollection->hasFlag(self::STOCK_FLAG));
    }
}

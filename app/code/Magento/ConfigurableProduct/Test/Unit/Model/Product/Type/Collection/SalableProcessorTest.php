<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\Collection;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class SalableProcessorTest extends \PHPUnit\Framework\TestCase
{
    const STOCK_FLAG = 'has_stock_status_filter';

    /** @var ObjectManager */
    private $objectManager;

    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Collection\SalableProcessor */
    protected $model;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $stockStatusFactory;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->stockStatusFactory = $this->getMockBuilder(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->objectManager->getObject(
            \Magento\ConfigurableProduct\Model\Product\Type\Collection\SalableProcessor::class,
            [
                'stockStatusFactory' => $this->stockStatusFactory,
            ]
        );
    }

    public function testProcess()
    {
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\Collection::class)
            ->setMethods(['addAttributeToFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $productCollection->expects($this->once())
            ->method('addAttributeToFilter')
            ->with(ProductInterface::STATUS, Status::STATUS_ENABLED)
            ->willReturnSelf();

        $stockStatusResource = $this->getMockBuilder(\Magento\CatalogInventory\Model\ResourceModel\Stock\Status::class)
            ->setMethods(['addStockDataToCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $stockStatusResource->expects($this->once())
            ->method('addStockDataToCollection')
            ->with($productCollection, true)
            ->willReturnSelf();

        $this->stockStatusFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($stockStatusResource);

        $this->model->process($productCollection);

        $this->assertTrue($productCollection->hasFlag(self::STOCK_FLAG));
    }
}

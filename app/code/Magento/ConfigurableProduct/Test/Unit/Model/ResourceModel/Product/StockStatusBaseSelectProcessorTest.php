<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\StockStatusBaseSelectProcessor;

class StockStatusBaseSelectProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StockStatusBaseSelectProcessor
     */
    private $subject;

    /**
     * @var StockConfigurationInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $stockConfigMock;

    /**
     * @var string
     */
    private $stockStatusTable = 'cataloginventory_stock_status';

    /**
     * @var StockStatusResource|\PHPUnit\Framework\MockObject\MockObject
     */
    private $stockStatusResourceMock;

    protected function setUp(): void
    {
        $this->stockConfigMock = $this->getMockBuilder(StockConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->stockStatusResourceMock = $this->getMockBuilder(StockStatusResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stockStatusResourceMock->expects($this->any())
            ->method('getMainTable')
            ->willReturn($this->stockStatusTable);

        $this->subject = (new ObjectManager($this))->getObject(
            StockStatusBaseSelectProcessor::class,
            [
                'stockConfig' => $this->stockConfigMock,
                'stockStatusResource' => $this->stockStatusResourceMock,
            ]
        );
    }

    /**
     * @param bool $isShowOutOfStock
     *
     * @dataProvider processDataProvider
     */
    public function testProcess($isShowOutOfStock)
    {
        $this->stockConfigMock->expects($this->any())
            ->method('isShowOutOfStock')
            ->willReturn($isShowOutOfStock);

        /** @var Select|\PHPUnit\Framework\MockObject\MockObject $selectMock */
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($isShowOutOfStock) {
            $selectMock->expects($this->once())
                ->method('joinInner')
                ->with(
                    ['stock' => $this->stockStatusTable],
                    sprintf(
                        'stock.product_id = %s.entity_id',
                        BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS
                    ),
                    []
                )
                ->willReturnSelf();
            $selectMock->expects($this->once())
                ->method('where')
                ->with(
                    'stock.stock_status = ?',
                    StockStatus::STATUS_IN_STOCK
                )
                ->willReturnSelf();
        } else {
            $selectMock->expects($this->never())
                ->method($this->anything());
        }

        $this->assertEquals($selectMock, $this->subject->process($selectMock));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'Out of stock products are being displayed' => [true],
            'Out of stock products are NOT being displayed' => [false],
        ];
    }
}

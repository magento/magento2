<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Ui\Component\Listing\Columns\StockStatus;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Class StatusTextTest
 */
class StockStatusTest extends AbstractColumnTest
{
    const STOCK_STATUS_ID = 1;
    const STOCK_STATUS_TEXT = 'In Stock';

    /**
     * @var Status|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $statusMock;

    protected function setUp()
    {
        parent::setUp();

        $this->statusMock = $this->getMockBuilder(StockStatus::class)
            ->setMethods(['getOptionText'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return StockStatus
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(StockStatus::class, [
            'context' => $this->contextMock,
            'uiComponentFactory' => $this->uiComponentFactoryMock,
            'status' => $this->statusMock,
            'components' => [],
            'data' => [],
        ]);
    }

    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        StockStatus::NAME => self::STOCK_STATUS_ID,
                    ]
                ],
            ],
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        StockStatus::NAME => self::STOCK_STATUS_ID,
                        '' => self::STOCK_STATUS_TEXT,
                    ]
                ],
            ],
        ];

        $this->statusMock->expects($this->once())
            ->method('getOptionText')
            ->with(self::STOCK_STATUS_ID)
            ->willReturn(self::STOCK_STATUS_TEXT);

        $this->assertEquals($expectedDataSource, $this->getModel()->prepareDataSource($dataSource));
    }
}

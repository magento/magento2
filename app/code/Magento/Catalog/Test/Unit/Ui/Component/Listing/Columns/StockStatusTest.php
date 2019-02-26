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
    /**
     * Test entity id
     */
    const ENTITY_ID = 1;

    /**
     * Text for product with stock status attribute code equals 1
     */
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
     * Prepare StockStatus ui component object
     *
     * @return StockStatus
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(
            StockStatus::class,
            [
                'context'            => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'status'             => $this->statusMock,
                'components'         => [],
                'data'               => [],
            ]
        );
    }

    /**
     * Check column value in product grid
     */
    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'entity_id' => self::ENTITY_ID,
                    ]
                ],
            ],
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'entity_id' => self::ENTITY_ID,
                        StockStatus::NAME => null,
                    ]
                ],
            ],
        ];

        $this->assertEquals($expectedDataSource, $this->getModel()->prepareDataSource($dataSource));
    }
}

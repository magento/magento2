<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Review\Ui\Component\Listing\Columns\Status;
use Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns\AbstractColumnTest;
use Magento\Review\Helper\Data as StatusSource;

/**
 * Class StatusTest
 */
class StatusTest extends AbstractColumnTest
{
    /**
     * @var StatusSource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceMock;

    protected function setUp()
    {
        parent::setUp();
        $this->sourceMock = $this->getMockBuilder(StatusSource::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Status
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(Status::class, [
            'context' => $this->contextMock,
            'uiComponentFactory' => $this->uiComponentFactoryMock,
            'components' => [],
            'data' => [],
            'source' => $this->sourceMock,
        ]);
    }

    public function testToOptionArray()
    {
        $expected = [
            'value' => 1,
            'label' => __('Approved'),
        ];

        $this->sourceMock->expects($this->once())
            ->method('getReviewStatusesOptionArray')
            ->willReturn($expected);

        $this->assertEquals($expected, $this->getModel()->toOptionArray());
    }

    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'status_id' => 1,
                    ]
                ],
            ],
        ];
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'status_id' => __('Approved'),
                    ]
                ],
            ],
        ];

        $this->sourceMock->expects($this->once())
            ->method('getReviewStatuses')
            ->willReturn([
                \Magento\Review\Model\Review::STATUS_APPROVED => __('Approved'),
            ]);

        $this->assertEquals($expectedDataSource, $this->getModel()->prepareDataSource($dataSource));
    }
}

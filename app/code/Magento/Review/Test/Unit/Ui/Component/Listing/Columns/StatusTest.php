<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns\AbstractColumnTestCase;
use Magento\Review\Helper\Data as StatusSource;
use Magento\Review\Model\Review;
use Magento\Review\Ui\Component\Listing\Columns\Status;
use PHPUnit\Framework\MockObject\MockObject;

class StatusTest extends AbstractColumnTestCase
{
    /**
     * @var StatusSource|MockObject
     */
    protected $sourceMock;

    protected function setUp(): void
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
                Review::STATUS_APPROVED => __('Approved'),
            ]);

        $this->assertEquals($expectedDataSource, $this->getModel()->prepareDataSource($dataSource));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Block\Adminhtml\Bulk\Details;

use Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\DoneButton;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Bulk\BulkStatusInterface;
use Magento\Framework\Bulk\OperationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoneButtonTest extends TestCase
{
    /**
     * @var DoneButton
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $bulkStatusMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->bulkStatusMock = $this->getMockForAbstractClass(BulkStatusInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->block = new DoneButton(
            $this->bulkStatusMock,
            $this->requestMock
        );
    }

    /**
     * @param int $failedCount
     * @param int $buttonsParam
     * @param array $expectedResult
     * @dataProvider getButtonDataProvider
     */
    public function testGetButtonData($failedCount, $buttonsParam, $expectedResult)
    {
        $uuid = 'some standard uuid string';
        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['uuid'], ['buttons'])
            ->willReturnOnConsecutiveCalls($uuid, $buttonsParam);
        $this->bulkStatusMock->expects($this->once())
            ->method('getOperationsCountByBulkIdAndStatus')
            ->with($uuid, OperationInterface::STATUS_TYPE_RETRIABLY_FAILED)
            ->willReturn($failedCount);

        $this->assertEquals($expectedResult, $this->block->getButtonData());
    }

    /**
     * @return array
     */
    public function getButtonDataProvider()
    {
        return [
            [1, 0, []],
            [0, 0, []],
            [
                0,
                1,
                [
                    'label' => __('Done'),
                    'class' => 'primary',
                    'sort_order' => 10,
                    'on_click' => '',
                    'data_attribute' => [
                        'mage-init' => [
                            'Magento_Ui/js/form/button-adapter' => [
                                'actions' => [
                                    [
                                        'targetName' => 'notification_area.notification_area.modalContainer.modal',
                                        'actionName' => 'closeModal'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ],
        ];
    }
}

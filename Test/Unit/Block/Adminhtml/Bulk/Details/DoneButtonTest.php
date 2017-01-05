<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Block\Adminhtml\Bulk\Details;

use Magento\Framework\Bulk\OperationInterface;

class DoneButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\DoneButton
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bulkStatusMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->bulkStatusMock = $this->getMock(\Magento\Framework\Bulk\BulkStatusInterface::class);
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();
        $this->block = new \Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\DoneButton(
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

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Block\Adminhtml\Bulk\Details;

class DoneButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\DoneButton
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $detailsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->detailsMock = $this->getMockBuilder(\Magento\AsynchronousOperations\Model\Operation\Details::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();
        $this->block = new \Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\DoneButton(
            $this->detailsMock,
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
        $details = ['failed_retriable' => $failedCount];
        $uuid = 'some standard uuid string';
        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['uuid'], ['buttons'])
            ->willReturnOnConsecutiveCalls($uuid, $buttonsParam);
        $this->detailsMock->expects($this->once())
            ->method('getDetails')
            ->with($uuid)
            ->willReturn($details);

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

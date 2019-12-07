<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Block\Adminhtml\Bulk\Details;

class RetryButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\RetryButton
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
        $this->block = new \Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\RetryButton(
            $this->detailsMock,
            $this->requestMock
        );
    }

    /**
     * @param int $failedCount
     * @param array $expectedResult
     * @dataProvider getButtonDataProvider
     */
    public function testGetButtonData($failedCount, $expectedResult)
    {
        $details = ['failed_retriable' => $failedCount];
        $uuid = 'some standard uuid string';
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('uuid')
            ->willReturn($uuid);
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
            [0, []],
            [
                20,
                [
                    'label' => __('Retry'),
                    'class' => 'retry primary',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'save']],
                        'form-role' => 'save',
                    ],
                ]
            ],
        ];
    }
}

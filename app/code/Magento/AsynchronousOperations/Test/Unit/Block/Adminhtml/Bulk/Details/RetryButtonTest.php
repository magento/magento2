<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Block\Adminhtml\Bulk\Details;

use Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\RetryButton;
use Magento\AsynchronousOperations\Model\Operation\Details;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RetryButtonTest extends TestCase
{
    /**
     * @var RetryButton
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $detailsMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->detailsMock = $this->createMock(Details::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->block = new RetryButton(
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

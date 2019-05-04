<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Test\Unit\Block\Adminhtml\Bulk\Details;

class BackButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\BackButton
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMock();
        $this->block = new \Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\BackButton(
            $this->urlBuilderMock
        );
    }

    public function testGetButtonData()
    {
        $backUrl = 'back url';
        $expectedResult = [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $backUrl),
            'class' => 'back',
            'sort_order' => 10
        ];

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/')
            ->willReturn($backUrl);

        $this->assertEquals($expectedResult, $this->block->getButtonData());
    }
}

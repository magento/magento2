<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\Design\Config\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Adminhtml\Design\Config\Edit\BackButton;

class BackButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BackButton
     */
    protected $block;

    /**
     * @var Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilder;

    protected function setUp(): void
    {
        $this->initContext();

        $this->block = new BackButton($this->context);
    }

    public function testGetButtonData()
    {
        $url = 'test';

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('*/*/')
            ->willReturn($url);

        $result = $this->block->getButtonData();

        $this->assertArrayHasKey('label', $result);
        $this->assertEquals($result['label'], __('Back'));
        $this->assertArrayHasKey('on_click', $result);
        $this->assertEquals($result['on_click'], "location.href = '$url';");
    }

    protected function initContext()
    {
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder(\Magento\Backend\Block\Widget\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilder);
    }
}

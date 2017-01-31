<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\Design\Config\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Adminhtml\Design\Config\Edit\BackButton;

class BackButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BackButton
     */
    protected $block;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    protected function setUp()
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
        $this->urlBuilder = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('Magento\Backend\Block\Widget\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilder);
    }
}

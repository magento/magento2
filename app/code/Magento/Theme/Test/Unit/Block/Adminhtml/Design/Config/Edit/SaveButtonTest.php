<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\Design\Config\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Theme\Block\Adminhtml\Design\Config\Edit\SaveButton;

class SaveButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveButton
     */
    protected $block;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    protected function setUp()
    {
        $this->initContext();

        $this->block = new SaveButton($this->context);
    }

    public function testGetButtonData()
    {
        $result = $this->block->getButtonData();

        $this->assertArrayHasKey('label', $result);
        $this->assertEquals($result['label'], __('Save Configuration'));
        $this->assertArrayHasKey('data_attribute', $result);
        $this->assertTrue(is_array($result['data_attribute']));
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

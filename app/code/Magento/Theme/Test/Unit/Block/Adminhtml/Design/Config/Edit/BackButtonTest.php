<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\Design\Config\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Adminhtml\Design\Config\Edit\BackButton;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackButtonTest extends TestCase
{
    /**
     * @var BackButton
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var UrlInterface|MockObject
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
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilder);
    }
}

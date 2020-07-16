<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\Design\Config\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Adminhtml\Design\Config\Edit\SaveButton;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveButtonTest extends TestCase
{
    /**
     * @var SaveButton
     */
    protected $block;

    /**
     * @var Context|MockObject
     */
    protected $context;

    protected function setUp(): void
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
        $this->assertIsArray($result['data_attribute']);
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

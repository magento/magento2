<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\UiComponent\Factory;

use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\UiComponent\Config\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Factory\HtmlContentFactory;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HtmlContentFactoryTest extends TestCase
{
    /**
     * @var Layout|MockObject
     */
    private $layout;

    /**
     * @var AbstractBlock|MockObject
     */
    private $block;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var HtmlContentFactory
     */
    private $htmlContentFactory;

    protected function setUp(): void
    {
        $this->layout = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->block = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->htmlContentFactory = new HtmlContentFactory();
    }

    public function testCreate()
    {
        $blockName = 'blockName';
        $bundleComponents[ManagerInterface::COMPONENT_ARGUMENTS_KEY]['block']['name'] = $blockName;
        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with($blockName)
            ->willReturn($this->block);
        $this->context->expects($this->once())
            ->method('getPageLayout')
            ->willReturn($this->layout);
        $this->assertTrue(
            $this->htmlContentFactory->create(
                $bundleComponents,
                [
                    'context' => $this->context
                ]
            )
        );
        $this->assertEquals($this->block, $bundleComponents[ManagerInterface::COMPONENT_ARGUMENTS_KEY]['block']);
    }
}

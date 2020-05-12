<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RendererListTest extends TestCase
{
    /**
     * @var RendererList
     */
    protected $renderList;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var AbstractBlock|MockObject
     */
    protected $blockMock;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->blockMock = $this->getMockBuilder(AbstractBlock::class)
            ->setMethods(['setRenderedBlock', 'getTemplate', 'setTemplate'])->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->setMethods(['getBlock', 'getChildName'])->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->willReturn($this->blockMock);

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->setMethods(['getLayout'])->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);

        $this->renderList = $objectManagerHelper->getObject(
            RendererList::class,
            ['context' => $this->contextMock]
        );
    }

    public function testGetRenderer()
    {
        $this->blockMock->expects($this->any())
            ->method('setRenderedBlock')
            ->willReturn($this->blockMock);

        $this->blockMock->expects($this->any())
            ->method('getTemplate')
            ->willReturn('template');

        $this->blockMock->expects($this->any())
            ->method('setTemplate')
            ->willReturn($this->blockMock);

        $this->layoutMock->expects($this->any())
            ->method('getChildName')
            ->willReturn(true);

        /** During the first call cache will be generated */
        $this->assertInstanceOf(
            BlockInterface::class,
            $this->renderList->getRenderer('type', null, null)
        );
        /** Cached value should be returned during second call */
        $this->assertInstanceOf(
            BlockInterface::class,
            $this->renderList->getRenderer('type', null, 'renderer_template')
        );
    }

    public function testGetRendererWithException()
    {
        $this->expectException('RuntimeException');
        $this->assertInstanceOf(
            BlockInterface::class,
            $this->renderList->getRenderer(null)
        );
    }
}

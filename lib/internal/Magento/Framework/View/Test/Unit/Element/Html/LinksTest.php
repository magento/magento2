<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element\Html;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Html\Links;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinksTest extends TestCase
{
    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerHelper;

    /** @var Links|MockObject */
    protected $block;

    /** @var Context|MockObject */
    protected $context;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);

        /** @var Context $context */
        $this->context = $this->objectManagerHelper->getObject(Context::class);
        $this->block = new Links($this->context);
    }

    public function testGetLinks()
    {
        $blocks = [0 => 'blocks'];
        $name = 'test_name';
        $this->context->getLayout()
            ->expects($this->once())
            ->method('getChildBlocks')
            ->with($name)
            ->willReturn($blocks);
        $this->block->setNameInLayout($name);
        $this->assertEquals($blocks, $this->block->getLinks());
    }

    public function testSetActive()
    {
        $link = $this->createMock(Link::class);
        $link
            ->expects($this->at(1))
            ->method('__call')
            ->with('setIsHighlighted', [true]);
        $link
            ->expects($this->at(0))
            ->method('__call')
            ->with('getPath', [])
            ->willReturn('test/path');

        $name = 'test_name';
        $this->context->getLayout()
            ->expects($this->once())
            ->method('getChildBlocks')
            ->with($name)
            ->willReturn([$link]);

        $this->block->setNameInLayout($name);
        $this->block->setActive('test/path');
    }

    public function testRenderLink()
    {
        $blockHtml = 'test';
        $name = 'test_name';
        $this->context->getLayout()
            ->expects($this->once())
            ->method('renderElement')
            ->with($name)
            ->willReturn($blockHtml);

        /** @var AbstractBlock $link */
        $link = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $link
            ->expects($this->once())
            ->method('getNameInLayout')
            ->willReturn($name);

        $this->assertEquals($blockHtml, $this->block->renderLink($link));
    }
}

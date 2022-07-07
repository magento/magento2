<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Items;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Layout;
use Magento\Sales\Block\Adminhtml\Items\AbstractItems;
use PHPUnit\Framework\TestCase;

class AbstractTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);
    }

    /**
     * @return void
     */
    public function testGetItemRenderer(): void
    {
        $renderer = $this->createMock(AbstractBlock::class);
        $layout = $this->createPartialMock(
            Layout::class,
            ['getChildName', 'getBlock', 'getGroupChildNames']
        );
        $layout->method('getChildName')
            ->with(null, 'some-type')
            ->willReturn('some-block-name');
        $layout->method('getBlock')
            ->with('some-block-name')
            ->willReturn($renderer);

        /** @var AbstractItems $block */
        $block = $this->_objectManager->getObject(
            AbstractItems::class,
            [
                'context' => $this->_objectManager->getObject(
                    Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $this->assertSame($renderer, $block->getItemRenderer('some-type'));
    }

    /**
     * @return void
     */
    public function testGetItemRendererThrowsExceptionForNonexistentRenderer(): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Renderer for type "some-type" does not exist.');
        $renderer = $this->createMock(\stdClass::class);
        $layout = $this->createPartialMock(
            Layout::class,
            ['getChildName', 'getBlock']
        );
        $layout->method('getChildName')
            ->with(null, 'some-type')
            ->willReturn('some-block-name');
        $layout->method('getBlock')
            ->with('some-block-name')
            ->willReturn($renderer);

        /** @var AbstractItems $block */
        $block = $this->_objectManager->getObject(
            AbstractItems::class,
            [
                'context' => $this->_objectManager->getObject(
                    Context::class,
                    ['layout' => $layout]
                )
            ]
        );

        $block->getItemRenderer('some-type');
    }
}

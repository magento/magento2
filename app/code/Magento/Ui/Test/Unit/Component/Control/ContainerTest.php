<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Control;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use Magento\Ui\Component\Control\Container;
use Magento\Ui\Component\Control\Item;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testToHtml()
    {
        $data = [];
        $id = 1;
        $nameInLayout = 'test-name';
        $blockName = $nameInLayout . '-' . $id . '-button';
        $expectedHtml = 'test html';

        $blockButtonMock = $this->createMock(Container::DEFAULT_CONTROL);
        $blockButtonMock->expects($this->once())->method('toHtml')->willReturn($expectedHtml);

        $contextMock = $this->createMock(Context::class);

        $eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $contextMock->expects($this->any())->method('getEventManager')->willReturn($eventManagerMock);

        $scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $scopeConfigMock->expects($this->any())->method('getValue')->withAnyParameters()->willReturn(false);
        $contextMock->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfigMock);

        $layoutMock = $this->createMock(Layout::class);
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Container::DEFAULT_CONTROL, $blockName)
            ->willReturn($blockButtonMock);
        $contextMock->expects($this->any())->method('getLayout')->willReturn($layoutMock);

        $itemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getId'])
            ->onlyMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $itemMock->expects($this->any())->method('getData')->willReturn($data);
        $itemMock->expects($this->any())->method('getId')->willReturn($id);

        $abstractContextMock = $this->getMockBuilder(AbstractBlock::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getNameInLayout'])
            ->getMockForAbstractClass();
        $abstractContextMock->expects($this->any())->method('getNameInLayout')->willReturn($nameInLayout);

        $container = new Container($contextMock);
        $container->setButtonItem($itemMock);
        $container->setData('context', $abstractContextMock);

        $this->assertEquals($expectedHtml, $container->toHtml());
    }
}

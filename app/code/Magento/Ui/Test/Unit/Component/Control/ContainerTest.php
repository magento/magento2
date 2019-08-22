<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Control;

use \Magento\Ui\Component\Control\Container;

class ContainerTest extends \PHPUnit\Framework\TestCase
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

        $contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);

        $eventManagerMock = $this->getMockForAbstractClass(\Magento\Framework\Event\ManagerInterface::class);
        $contextMock->expects($this->any())->method('getEventManager')->willReturn($eventManagerMock);

        $scopeConfigMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $scopeConfigMock->expects($this->any())->method('getValue')->withAnyParameters()->willReturn(false);
        $contextMock->expects($this->any())->method('getScopeConfig')->willReturn($scopeConfigMock);

        $layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->with(Container::DEFAULT_CONTROL, $blockName)
            ->willReturn($blockButtonMock);
        $contextMock->expects($this->any())->method('getLayout')->willReturn($layoutMock);

        $itemMock = $this->createPartialMock(\Magento\Ui\Component\Control\Item::class, ['getId', 'getData']);
        $itemMock->expects($this->any())->method('getData')->willReturn($data);
        $itemMock->expects($this->any())->method('getId')->willReturn($id);

        $abstractContextMock = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNameInLayout'])
            ->getMockForAbstractClass();
        $abstractContextMock->expects($this->any())->method('getNameInLayout')->willReturn($nameInLayout);

        $container = new Container($contextMock);
        $container->setButtonItem($itemMock);
        $container->setData('context', $abstractContextMock);

        $this->assertEquals($expectedHtml, $container->toHtml());
    }
}

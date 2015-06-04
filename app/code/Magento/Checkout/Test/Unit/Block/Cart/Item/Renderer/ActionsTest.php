<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Checkout\Block\Cart\Item\Renderer\Context;

class ActionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Actions
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getMockForAbstractClass();

        $this->layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->getMockForAbstractClass();

        $this->model = $objectManagerHelper->getObject(
            'Magento\Checkout\Block\Cart\Item\Renderer\Actions',
            [
                'scopeConfig' => $this->scopeConfigMock,
                'layout' => $this->layoutMock,
            ]
        );
    }

    public function testGetItemContext()
    {
        /**
         * @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder('Magento\Checkout\Block\Cart\Item\Renderer\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setItemContext($contextMock);
        $this->assertEquals($contextMock, $this->model->getItemContext());
    }

    public function testToHtml()
    {
        $childNameOne = 'child.1';
        $childNameTextOne = 'child.1 text';
        $childNameTwo = 'child.2';
        $childNames = [$childNameOne, $childNameTwo];

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        /**
         * @var Context|\PHPUnit_Framework_MockObject_MockObject $contextMock
         */
        $contextMock = $this->getMockBuilder('Magento\Checkout\Block\Cart\Item\Renderer\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model->setItemContext($contextMock);

        $this->layoutMock->expects($this->once())
            ->method('getChildNames')
            ->with($this->model->getNameInLayout())
            ->willReturn($childNames);

        /** @var Generic|\PHPUnit_Framework_MockObject_MockObject $childMockOne */
        $childMockOne = $this->getMockBuilder('Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic')
            ->disableOriginalConstructor()
            ->getMock();
        $childMockOne->expects($this->once())
            ->method('setItemContext')
            ->with($contextMock);

        $childMockTwo = false;

        $this->layoutMock->expects($this->once())
            ->method('renderElement')
            ->with($childNameOne, false)
            ->willReturn($childNameTextOne);
        $this->layoutMock->expects($this->exactly(2))
            ->method('getBlock')
            ->willReturnMap(
                [
                    [$childNameOne, $childMockOne],
                    [$childNameTwo, $childMockTwo],
                ]
            );

        $this->assertEquals($childNameTextOne, $this->model->toHtml());
    }
}

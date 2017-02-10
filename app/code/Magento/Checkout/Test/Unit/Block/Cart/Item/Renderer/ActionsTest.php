<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Quote\Model\Quote\Item;

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

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManagerHelper->getObject(
            \Magento\Checkout\Block\Cart\Item\Renderer\Actions::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'layout' => $this->layoutMock,
            ]
        );
    }

    public function testGetItem()
    {
        /**
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($this->model, $this->model->setItem($itemMock));
        $this->assertEquals($itemMock, $this->model->getItem());
    }

    public function testToHtml()
    {
        $childNameOne = 'child.1';
        $childNameTextOne = 'child.1 text';
        $childNameTwo = 'child.2';
        $childNames = [$childNameOne, $childNameTwo];

        /**
         * @var Item|\PHPUnit_Framework_MockObject_MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model->setItem($itemMock);

        $this->layoutMock->expects($this->once())
            ->method('getChildNames')
            ->with($this->model->getNameInLayout())
            ->willReturn($childNames);

        /** @var Generic|\PHPUnit_Framework_MockObject_MockObject $childMockOne */
        $childMockOne = $this->getMockBuilder(\Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic::class)
            ->disableOriginalConstructor()
            ->getMock();
        $childMockOne->expects($this->once())
            ->method('setItem')
            ->with($itemMock);

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

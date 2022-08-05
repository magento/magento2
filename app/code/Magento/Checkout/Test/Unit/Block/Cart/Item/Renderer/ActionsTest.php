<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer\Actions;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions\Generic;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ActionsTest extends TestCase
{
    /**
     * @var Actions
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

        $this->model = $objectManagerHelper->getObject(
            Actions::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'layout' => $this->layoutMock,
            ]
        );
    }

    public function testGetItem()
    {
        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
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

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        /**
         * @var Item|MockObject $itemMock
         */
        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model->setItem($itemMock);

        $this->layoutMock->expects($this->once())
            ->method('getChildNames')
            ->with($this->model->getNameInLayout())
            ->willReturn($childNames);

        /** @var Generic|MockObject $childMockOne */
        $childMockOne = $this->getMockBuilder(Generic::class)
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

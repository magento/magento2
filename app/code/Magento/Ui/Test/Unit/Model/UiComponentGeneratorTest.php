<?php
/**
 * Copyright © Magento, Inc. All rights reserved. 
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\LayoutInterface;
use Magento\Framework\View\Element\UiComponentInterface;

class UiComponentGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Ui\Model\UiComponentGenerator */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextFactoryMock;

    /** @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $uiComponentFactoryMock;

    protected function setUp()
    {
        $this->contextFactoryMock = $this
            ->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiComponentFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Ui\Model\UiComponentGenerator::class,
            [
                'contextFactory' => $this->contextFactoryMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock
            ]
        );
    }

    public function testGenerateUiComponent()
    {
        $uiComponentMock = $this->getMock(UiComponentInterface::class);
        $uiComponentMockChild1 = $this->getMock(UiComponentInterface::class);
        $uiComponentMockChild2 = $this->getMock(UiComponentInterface::class);
        $uiComponentMockChild1->expects($this->once())
            ->method('prepare');
        $uiComponentMockChild2->expects($this->once())
            ->method('prepare');
        $uiComponentMock->expects($this->once())
            ->method('prepare');
        $uiComponentMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([$uiComponentMockChild1, $uiComponentMockChild2]);
        $this->uiComponentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($uiComponentMock);
        $layoutMock = $this->getMock(\Magento\Framework\View\LayoutInterface::class);
        $this->model->generateUiComponent('widget_recently_viewed', $layoutMock);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\ContextFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\LayoutInterface as LayoutInterfaceView;
use Magento\Ui\Model\UiComponentGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UiComponentGeneratorTest extends TestCase
{
    /** @var UiComponentGenerator */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ContextFactory|MockObject */
    protected $contextFactoryMock;

    /** @var UiComponentFactory|MockObject */
    protected $uiComponentFactoryMock;

    protected function setUp(): void
    {
        $this->contextFactoryMock = $this
            ->getMockBuilder(ContextFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->uiComponentFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            UiComponentGenerator::class,
            [
                'contextFactory' => $this->contextFactoryMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock
            ]
        );
    }

    public function testGenerateUiComponent()
    {
        $uiComponentMock = $this->getMockForAbstractClass(UiComponentInterface::class);
        $uiComponentMockChild1 = $this->getMockForAbstractClass(UiComponentInterface::class);
        $uiComponentMockChild2 = $this->getMockForAbstractClass(UiComponentInterface::class);
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
        $layoutMock = $this->createMock(LayoutInterfaceView::class);
        $this->model->generateUiComponent('widget_recently_viewed', $layoutMock);
    }
}

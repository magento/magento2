<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\Generator;

use \Magento\Framework\View\Layout\Generator\UiComponent;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class UiComponentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ScheduledStructure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduledStructureMock;

    /**
     * @var Layout\Reader\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerContextMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactoryMock;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $argumentInterpreterMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextFactoryMock;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $blockFactoryMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\UiComponent
     */
    protected $uiComponent;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->argumentInterpreterMock = $this->getMockBuilder('Magento\Framework\Data\Argument\InterpreterInterface')
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->uiComponentFactoryMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentFactory')
            ->disableOriginalConstructor()->getMock();
        $this->scheduledStructureMock = $this->getMockBuilder('Magento\Framework\View\Layout\ScheduledStructure')
            ->disableOriginalConstructor()->getMock();
        $this->contextFactoryMock = $this->getMock(
            'Magento\Framework\View\Element\UiComponent\ContextFactory',
            [],
            [],
            '',
            false
        );
        $this->blockFactoryMock = $this->getMock(
            'Magento\Framework\View\Element\BlockFactory',
            [],
            [],
            '',
            false
        );

        $this->uiComponent = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Layout\Generator\UiComponent',
            [
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'blockFactory' => $this->blockFactoryMock,
                'contextFactory' => $this->contextFactoryMock
            ]
        );
    }

    public function testProcess()
    {
        $this->prepareScheduledStructure();

        $this->readerContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Reader\Context')
            ->disableOriginalConstructor()->getMock();

        $this->readerContextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $generatorContextMock = $this->getMockBuilder('Magento\Framework\View\Layout\Generator\Context')
            ->disableOriginalConstructor()->getMock();

        $structureMock = $this->getMockBuilder('Magento\Framework\View\Layout\Data\Structure')
            ->disableOriginalConstructor()->getMock();

        $structureMock->expects($this->once())
            ->method('addToParentGroup')
            ->with(UiComponent::TYPE, 'new_group')
            ->willReturnSelf();

        $layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')->getMockForAbstractClass();

        $generatorContextMock->expects($this->any())
            ->method('getStructure')
            ->willReturn($structureMock);
        $generatorContextMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $this->uiComponentFactoryMock->expects($this->any())
            ->method('setLayout')
            ->with($layoutMock)
            ->willReturnSelf();

        $componentMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponentInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );

        $contextMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $blockMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\BlockInterface',
            [],
            '',
            false
        );

        $this->contextFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'namespace' => 'uiComponent',
                    'pageLayout' => $layoutMock
                ]
            )->willReturn($contextMock);

        $this->uiComponentFactoryMock->expects($this->any())
            ->method('create')
            ->with(
                'uiComponent',
                null,
                ['context' => $contextMock]
            )->willReturn($componentMock);

        $this->blockFactoryMock->expects($this->once())
            ->method('createBlock')
            ->with(UiComponent::CONTAINER, ['component' => $componentMock])
            ->willReturn($blockMock);

        $this->argumentInterpreterMock->expects($this->any())
            ->method('evaluate')
            ->will($this->returnValueMap([
                [['key_1' => 'value_1'], 'value_1'],
                [['key_2' => 'value_2'], 'value_2'],
            ]));

        $layoutMock->expects($this->any())
            ->method('setBlock')
            ->with(UiComponent::TYPE, $blockMock)
            ->willReturnSelf();

        $this->uiComponent->process($this->readerContextMock, $generatorContextMock);
    }

    protected function prepareScheduledStructure()
    {
        $this->scheduledStructureMock->expects($this->any())
            ->method('getElements')
            ->willReturn([
                UiComponent::TYPE => [
                    UiComponent::TYPE,
                    [
                        'attributes' => [
                            'group'   => 'new_group',
                            'component' => 'component_name',
                        ],
                        'arguments'  => [
                            'attribute_1' => ['key_1' => 'value_1'],
                            'attribute_2' => ['key_2' => 'value_2'],
                        ]
                    ],
                ],
            ]);
    }
}

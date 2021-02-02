<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout\Generator;

use \Magento\Framework\View\Layout\Generator\UiComponent;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UiComponentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ScheduledStructure|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scheduledStructureMock;

    /**
     * @var Layout\Reader\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $readerContextMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $uiComponentFactoryMock;

    /**
     * @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $argumentInterpreterMock;

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextFactoryMock;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $blockFactoryMock;

    /**
     * @var \Magento\Framework\View\Layout\Generator\UiComponent
     */
    protected $uiComponent;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->argumentInterpreterMock = $this->getMockBuilder(
            \Magento\Framework\Data\Argument\InterpreterInterface::class
        )->disableOriginalConstructor()->getMockForAbstractClass();
        $this->uiComponentFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentFactory::class)
            ->setMethods(['setLayout', 'create'])
            ->disableOriginalConstructor()->getMock();
        $this->scheduledStructureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\ScheduledStructure::class)
            ->disableOriginalConstructor()->getMock();
        $this->contextFactoryMock =
            $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextFactory::class);
        $this->blockFactoryMock = $this->createMock(\Magento\Framework\View\Element\BlockFactory::class);

        $this->uiComponent = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Layout\Generator\UiComponent::class,
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

        $this->readerContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Reader\Context::class)
            ->disableOriginalConstructor()->getMock();

        $this->readerContextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $generatorContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Context::class)
            ->disableOriginalConstructor()->getMock();

        $structureMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Data\Structure::class)
            ->disableOriginalConstructor()->getMock();

        $structureMock->expects($this->once())
            ->method('addToParentGroup')
            ->with(UiComponent::TYPE, 'new_group')
            ->willReturnSelf();

        $layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)->getMockForAbstractClass();

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
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $contextMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false
        );
        $blockMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\BlockInterface::class,
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
                ['context' => $contextMock, 'structure' => $structureMock]
            )->willReturn($componentMock);

        $this->blockFactoryMock->expects($this->once())
            ->method('createBlock')
            ->with(UiComponent::CONTAINER, ['component' => $componentMock])
            ->willReturn($blockMock);

        $this->argumentInterpreterMock->expects($this->any())
            ->method('evaluate')
            ->willReturnMap([
                [['key_1' => 'value_1'], 'value_1'],
                [['key_2' => 'value_2'], 'value_2'],
            ]);

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

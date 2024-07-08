<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Generator;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponent\ContextFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Framework\View\Layout\Generator\UiComponent;
use Magento\Framework\View\Layout\Reader\Context;
use Magento\Framework\View\Layout\ScheduledStructure;

use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UiComponentTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var ScheduledStructure|MockObject
     */
    protected $scheduledStructureMock;

    /**
     * @var Layout\Reader\Context|MockObject
     */
    protected $readerContextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    protected $uiComponentFactoryMock;

    /**
     * @var InterpreterInterface|MockObject
     */
    protected $argumentInterpreterMock;

    /**
     * @var ContextFactory|MockObject
     */
    protected $contextFactoryMock;

    /**
     * @var BlockFactory|MockObject
     */
    protected $blockFactoryMock;

    /**
     * @var UiComponent
     */
    protected $uiComponent;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->argumentInterpreterMock = $this->getMockBuilder(
            InterpreterInterface::class
        )->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->uiComponentFactoryMock = $this->getMockBuilder(UiComponentFactory::class)
            ->addMethods(['setLayout'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduledStructureMock = $this->getMockBuilder(ScheduledStructure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextFactoryMock =
            $this->createMock(ContextFactory::class);
        $this->blockFactoryMock = $this->createMock(BlockFactory::class);

        $this->uiComponent = $this->objectManagerHelper->getObject(
            UiComponent::class,
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

        $this->readerContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->readerContextMock->expects($this->any())
            ->method('getScheduledStructure')
            ->willReturn($this->scheduledStructureMock);

        $generatorContextMock = $this->getMockBuilder(\Magento\Framework\View\Layout\Generator\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $structureMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $structureMock->expects($this->once())
            ->method('addToParentGroup')
            ->with(UiComponent::TYPE, 'new_group')
            ->willReturnSelf();

        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();

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
            UiComponentInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $contextMock = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            false
        );
        $blockMock = $this->getMockForAbstractClass(
            BlockInterface::class,
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

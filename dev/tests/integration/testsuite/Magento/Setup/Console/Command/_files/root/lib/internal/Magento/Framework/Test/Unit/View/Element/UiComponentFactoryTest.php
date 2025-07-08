<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\View\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\DataProvider\Sanitizer;
use PHPUnit\Framework\MockObject\MockObject;

class UiComponentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\View\Element\UiComponentFactory */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|MockObject */
    protected $objectManagerMock;

    /** @var \Magento\Framework\Data\Argument\InterpreterInterface|MockObject */
    protected $interpreterMock;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextFactory|MockObject */
    protected $contextFactoryMock;

    /** @var \Magento\Framework\Config\DataInterfaceFactory|MockObject */
    protected $dataInterfaceFactoryMock;

    /** @var \Magento\Ui\Config\Reader\Definition\Data|MockObject */
    protected $dataMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->interpreterMock = $this->getMockBuilder(\Magento\Framework\Data\Argument\InterpreterInterface::class)
            ->getMockForAbstractClass();
        $this->contextFactoryMock = $this
            ->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataInterfaceFactoryMock = $this->getMockBuilder(\Magento\Framework\Config\DataInterfaceFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataMock = $this->createMock(\Magento\Framework\Config\DataInterface::class);
        $sanitizerMock = $this->createMock(Sanitizer::class);
        $sanitizerMock->method('sanitize')->willReturnArgument(0);
        $sanitizerMock->method('sanitizeComponentMetadata')->willReturnArgument(0);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Element\UiComponentFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'argumentInterpreter' => $this->interpreterMock,
                'contextFactory' => $this->contextFactoryMock,
                'configFactory' => $this->dataInterfaceFactoryMock,
                'data' => [],
                'componentChildFactories' => [],
                'definitionData' => $this->dataMock,
                'sanitizer' => $sanitizerMock
            ]
        );
    }

    public function testCreateRootComponent()
    {
        $identifier = "product_listing";
        $context = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $bundleComponents = [
            'attributes' => [
                'class' => 'Some\Class\Component',
            ],
            'arguments' => [
                'config' => [
                    'class' => 'Some\Class\Component2'
                ]
            ],
            'children' => []
        ];
        $uiConfigMock = $this->createMock(\Magento\Framework\Config\DataInterface::class);
        $this->dataInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($uiConfigMock);
        $uiConfigMock->expects($this->once())
            ->method('get')
            ->willReturn($bundleComponents);

        $this->contextFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($context);
        $expectedArguments = [
            'config' => [
                'class' => 'Some\Class\Component2'
            ],
            'data' => [
                'name' => $identifier
            ],
            'context' => $context,
            'components' => []
        ];
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('Some\Class\Component2', $expectedArguments);
        $this->model->create($identifier);
    }

    public function testNonRootComponent()
    {
        $identifier = "custom_select";
        $name = "fieldset";
        $context = $this->createMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $arguments = ['context' => $context];
        $definitionArguments = [
            'componentType' => 'select',
            'attributes' => [
                'class' => '\Some\Class',
            ],
            'arguments' => []
        ];
        $expectedArguments = [
            'data' => [
                'name' => $identifier
            ],
            'context' => $context,
            'components' => []
        ];
        $this->dataMock->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($definitionArguments);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('\Some\Class', $expectedArguments);
        $this->model->create($identifier, $name, $arguments);
    }
}

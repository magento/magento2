<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit\View\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class UiComponentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\View\Element\UiComponentFactory */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var \Magento\Framework\Data\Argument\InterpreterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $interpreterMock;

    /** @var \Magento\Framework\View\Element\UiComponent\ContextFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextFactoryMock;

    /** @var \Magento\Framework\Config\DataInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataInterfaceFactoryMock;

    /** @var \SafeReflectionClass|\PHPUnit_Framework_MockObject_MockObject */
    protected $safeReflectionClassMock;

    /** @var \SafeReflectionClass|\PHPUnit_Framework_MockObject_MockObject */
    protected $safeReflectionClassMock2;

    /** @var \Magento\Ui\Config\Reader\Definition\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->interpreterMock = $this->getMockBuilder(\Magento\Framework\Data\Argument\InterpreterInterface::class)
            ->getMockForAbstractClass();
        $this->contextFactoryMock = $this
            ->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataInterfaceFactoryMock = $this->getMockBuilder(\Magento\Framework\Config\DataInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->safeReflectionClassMock = $this->getMockBuilder(\SafeReflectionClass::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->safeReflectionClassMock2 = $this->getMockBuilder(\SafeReflectionClass::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataMock = $this->getMock(\Magento\Framework\Config\DataInterface::class);
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
                'definitionData' => $this->dataMock
            ]
        );
    }

    public function testCreateRootComponent()
    {
        $identifier = "product_listing";
        $context = $this->getMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
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
        $uiConfigMock = $this->getMock(\Magento\Framework\Config\DataInterface::class);
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
        $context = $this->getMock(\Magento\Framework\View\Element\UiComponent\ContextInterface::class);
        $arguments = ['context' => $context];
        $defintionArguments = [
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
            ->willReturn($defintionArguments);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with('\Some\Class', $expectedArguments);
        $this->model->create($identifier, $name, $arguments);
    }
}

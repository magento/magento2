<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\MassAction\Columns;

use Magento\Ui\Component\MassAction\Columns\Column;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ColumnTest
 */
class ColumnTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var Column
     */
    protected $column;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->column = $this->objectManager->getObject(
            \Magento\Ui\Component\MassAction\Columns\Column::class,
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends'
                    ]
                ]
            ]
        );
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        $this->assertTrue($this->column->getComponentName() === Column::NAME);
    }

    /**
     * Run test prepareItems method
     *
     * @return void
     */
    public function testPrepareItems()
    {
        $testItems = ['item1','item2', 'item3'];

        $this->assertEquals($testItems, $this->column->prepareItems($testItems));
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepare()
    {
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $this->column = $this->objectManager->getObject(
            \Magento\Ui\Component\MassAction\Columns\Column::class,
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => []
                ]
            ]
        );

        $this->contextMock->expects($this->once())
            ->method('getNamespace')
            ->willReturn('test_namespace');
        $this->contextMock->expects($this->once())
            ->method('addComponentDefinition')
            ->with($this->column->getComponentName(), ['extends' => 'test_namespace']);

        $this->column->prepare();
    }

    /**
     * Run test prepare method
     *
     * @return void
     */
    public function testPrepareExtendsFromConfig()
    {
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $this->contextMock->expects($this->never())
            ->method('getNamespace');
        $this->contextMock->expects($this->once())
            ->method('addComponentDefinition')
            ->with($this->column->getComponentName(), ['extends' => 'test_config_extends']);

        $this->column->prepare();
    }
}

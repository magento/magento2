<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\MassAction\Columns;

use Magento\Ui\Component\MassAction\Columns\Column;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ColumnTest
 */
class ColumnTest extends \PHPUnit_Framework_TestCase
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
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);

        $this->column = $this->objectManager->getObject(
            'Magento\Ui\Component\MassAction\Columns\Column',
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
        $this->column = $this->objectManager->getObject(
            'Magento\Ui\Component\MassAction\Columns\Column',
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
        $this->contextMock->expects($this->never())
            ->method('getNamespace');
        $this->contextMock->expects($this->once())
            ->method('addComponentDefinition')
            ->with($this->column->getComponentName(), ['extends' => 'test_config_extends']);

        $this->column->prepare();
    }
}

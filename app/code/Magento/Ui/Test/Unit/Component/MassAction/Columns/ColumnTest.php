<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\MassAction\Columns;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Component\MassAction\Columns\Column;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
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
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->column = $this->objectManager->getObject(
            Column::class,
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
        $this->assertSame(Column::NAME, $this->column->getComponentName());
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
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $this->column = $this->objectManager->getObject(
            Column::class,
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
        $processor = $this->getMockBuilder(Processor::class)
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

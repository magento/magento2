<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use PHPUnit\Framework\TestCase;

/**
 * Testing for generic UI column classes & for custom ones such as Websites
 */
class ColumnTest extends TestCase
{
    /**
     * @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactoryMock;

    protected $dataProviderMock;

    /**
     * @var string
     */
    protected $columnClass = Column::class;

    /**
     * @var string
     */
    protected $columnName = Column::NAME;

    /**
     * Set up
     */
    protected function setUp(): void
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

        $this->uiComponentFactoryMock = $this->createMock(UiComponentFactory::class);
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        $column = $this->objectManager->getObject(
            $this->columnClass,
            [
                'context' => $this->contextMock,
                'data' => [
                    'js_config' => [
                        'extends' => 'test_config_extends'
                    ],
                    'config' => [
                        'dataType' => 'testType'
                    ]
                ]
            ]
        );

        $this->assertEquals($column->getComponentName(), $this->columnName . '.testType');
    }

    /**
     * Run test prepareItems method
     *
     * @return void
     */
    public function testPrepareItems()
    {
        $testItems = ['item1','item2', 'item3'];
        $column = $this->objectManager->getObject(
            $this->columnClass,
            ['context' => $this->contextMock]
        );

        $this->assertEquals($testItems, $column->prepareItems($testItems));
    }

    /**
     * Run test prepare method
     *
     * @param null $dataProviderMock
     * @return void
     */
    public function testPrepare()
    {
        $data = [
            'name' => 'test_name',
            'js_config' => ['extends' => 'test_config_extends'],
            'config' => ['dataType' => 'test_type', 'sortable' => true]
        ];

        /** @var Column $column */
        $column = $this->objectManager->getObject(
            $this->columnClass,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'data' => $data
            ]
        );

        /** @var UiComponentInterface|PHPUnit\Framework\MockObject\MockObject $wrappedComponentMock */
        $wrappedComponentMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class,
            [],
            '',
            false
        );

        if ($this->dataProviderMock === null) {
            $this->dataProviderMock = $this->getMockForAbstractClass(
                DataProviderInterface::class,
                [],
                '',
                false
            );

            $this->dataProviderMock->expects($this->once())
                ->method('addOrder')
                ->with('test_name', 'ASC');
        }

        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects($this->atLeastOnce())
            ->method('getProcessor')
            ->willReturn($processor);
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getNamespace')
            ->willReturn('test_namespace');
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);
        $this->contextMock->expects($this->atLeastOnce())
            ->method('getRequestParam')
            ->with('sorting')
            ->willReturn(['field' => 'test_name', 'direction' => 'asc']);
        $this->contextMock->expects($this->atLeastOnce())
            ->method('addComponentDefinition')
            ->with($this->columnName . '.test_type', ['extends' => 'test_config_extends']);

        $this->uiComponentFactoryMock->expects($this->once())
            ->method('create')
            ->with('test_name', 'test_type', array_merge(['context' => $this->contextMock], $data))
            ->willReturn($wrappedComponentMock);

        $wrappedComponentMock->expects($this->once())
            ->method('getContext')
            ->willReturn($this->contextMock);
        $wrappedComponentMock->expects($this->once())
            ->method('prepare');

        $column->prepare();
    }

    /**
     * Run a test on sorting function
     *
     * @param array $config
     * @param string $direction
     * @param int $numOfProviderCalls
     * @throws \ReflectionException
     *
     * @dataProvider sortingDataProvider
     */
    public function testSorting(array $config, string $direction, int $numOfProviderCalls)
    {
        $data = [
            'name' => 'test_name',
            'config' => $config
        ];

        $this->dataProviderMock = $this->getMockForAbstractClass(
            DataProviderInterface::class,
            [],
            '',
            false
        );

        $this->dataProviderMock->expects($this->exactly($numOfProviderCalls))
            ->method('addOrder')
            ->with('test_name', $direction);

        $this->contextMock->expects($this->atLeastOnce())
            ->method('getRequestParam')
            ->with('sorting')
            ->willReturn(['field' => 'test_name', 'direction' => $direction]);

        $this->contextMock->expects($this->exactly($numOfProviderCalls))
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);

        $column = $this->objectManager->getObject(
            $this->columnClass,
            [
                'context' => $this->contextMock,
                'uiComponentFactory' => $this->uiComponentFactoryMock,
                'data' => $data
            ]
        );

        // get access to the method
        $method = new \ReflectionMethod(
            Column::class,
            'applySorting'
        );
        $method->setAccessible(true);

        $method->invokeArgs($column, []);
    }

    public function sortingDataProvider()
    {
        return [
            [['dataType' => 'test_type', 'sortable' => true], 'ASC', 1],
            [['dataType' => 'test_type', 'sortable' => false], 'ASC', 0],
            [['dataType' => 'test_type', 'sortable' => true], 'foobar', 0]
        ];
    }
}

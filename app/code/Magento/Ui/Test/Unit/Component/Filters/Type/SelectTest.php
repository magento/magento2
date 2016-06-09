<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\Type\AbstractFilter;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class SelectTest
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var UiComponentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \Magento\Ui\Component\Filters\FilterModifier|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterModifierMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->contextMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(
            'Magento\Framework\Api\FilterBuilder',
            [],
            [],
            '',
            false
        );
        $this->filterModifierMock = $this->getMock(
            'Magento\Ui\Component\Filters\FilterModifier',
            ['applyFilterModifier'],
            [],
            '',
            false
        );
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $date = new Select(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            null,
            []
        );

        $this->assertTrue($date->getComponentName() === Select::NAME);
    }

    /**
     * Run test prepare method
     *
     * @param array $data
     * @param array $filterData
     * @param array|null $expectedCondition
     * @dataProvider getPrepareDataProvider
     * @return void
     */
    public function testPrepare($data, $filterData, $expectedCondition)
    {
        $name = $data['name'];
        /** @var UiComponentInterface $uiComponent */
        $uiComponent = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponentInterface',
            [],
            '',
            false
        );

        $uiComponent->expects($this->any())
            ->method('getContext')
            ->willReturn($this->contextMock);

        $this->contextMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn(Select::NAME);
        $this->contextMock->expects($this->any())
            ->method('addComponentDefinition')
            ->with(Select::NAME, ['extends' => Select::NAME]);
        $this->contextMock->expects($this->any())
            ->method('getFiltersParams')
            ->willReturn($filterData);
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface',
            ['addFilter'],
            '',
            false
        );
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProvider);

        if ($expectedCondition !== null) {
            $filterMock = $this->getMock('Magento\Framework\Api\Filter');
            $this->filterBuilderMock->expects($this->any())
                ->method('setConditionType')
                ->with($expectedCondition)
                ->willReturnSelf();
            $this->filterBuilderMock->expects($this->any())
                ->method('setField')
                ->with($name)
                ->willReturnSelf();
            $this->filterBuilderMock->expects($this->any())
                ->method('setValue')
                ->willReturnSelf();
            $this->filterBuilderMock->expects($this->any())
                ->method('create')
                ->willReturn($filterMock);
            $dataProvider->expects($this->any())
                ->method('addFilter')
                ->with($filterMock);
        }

        /** @var \Magento\Framework\Data\OptionSourceInterface $selectOptions */
        $selectOptions = $this->getMockForAbstractClass(
            'Magento\Framework\Data\OptionSourceInterface',
            [],
            '',
            false
        );

        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->with($name, Select::COMPONENT, ['context' => $this->contextMock, 'options' => $selectOptions])
            ->willReturn($uiComponent);

        $date = new Select(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            $selectOptions,
            [],
            $data
        );

        $date->prepare();
    }

    /**
     * @return array
     */
    public function getPrepareDataProvider()
    {
        return [
            [
                ['name' => 'test_date', 'config' => []],
                [],
                null
            ],
            [
                ['name' => 'test_date', 'config' => []],
                ['test_date' => ''],
                'eq'
            ],
            [
                ['name' => 'test_date', 'config' => ['dataType' => 'text']],
                ['test_date' => 'some_value'],
                'eq'
            ],
            [
                ['name' => 'test_date', 'config' => ['dataType' => 'select']],
                ['test_date' => ['some_value1', 'some_value2']],
                'in'
            ],
            [
                ['name' => 'test_date', 'config' => ['dataType' => 'multiselect']],
                ['test_date' => 'some_value'],
                'finset'
            ],
        ];
    }
}

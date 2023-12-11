<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    protected $contextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    protected $uiComponentFactory;

    /**
     * @var FilterBuilder|MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var FilterModifier|MockObject
     */
    protected $filterModifierMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockForAbstractClass(
            ContextInterface::class,
            [],
            '',
            false
        );
        $this->uiComponentFactory = $this->createPartialMock(
            UiComponentFactory::class,
            ['create']
        );
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->filterModifierMock = $this->createPartialMock(
            FilterModifier::class,
            ['applyFilterModifier']
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
        $date = new Select(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            null,
            []
        );

        $this->assertSame(Select::NAME, $date->getComponentName());
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
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $name = $data['name'];
        /** @var UiComponentInterface $uiComponent */
        $uiComponent = $this->getMockForAbstractClass(
            UiComponentInterface::class,
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
            DataProviderInterface::class,
            ['addFilter'],
            '',
            false
        );
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProvider);

        if ($expectedCondition !== null) {
            $filterMock = $this->createMock(Filter::class);
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

        /** @var OptionSourceInterface $selectOptions */
        $selectOptions = $this->getMockForAbstractClass(
            OptionSourceInterface::class,
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

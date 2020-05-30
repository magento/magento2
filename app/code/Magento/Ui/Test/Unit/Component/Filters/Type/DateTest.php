<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Date;
use Magento\Ui\Component\Form\Element\DataType\Date as FormDate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Date grid filter functionality
 */
class DateTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    private $contextMock;

    /**
     * @var UiComponentFactory|MockObject
     */
    private $uiComponentFactory;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilderMock;

    /**
     * @var FilterModifier|MockObject
     */
    private $filterModifierMock;

    /**
     * @var DataProviderInterface|MockObject
     */
    private $dataProviderMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $this->uiComponentFactory = $this->getMockBuilder(UiComponentFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterModifierMock = $this->getMockBuilder(FilterModifier::class)
            ->setMethods(['applyFilterModifier'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProviderMock = $this->getMockForAbstractClass(DataProviderInterface::class);
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->contextMock->expects(static::never())->method('getProcessor');
        $date = new Date(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            []
        );

        static::assertSame(Date::NAME, $date->getComponentName());
    }

    /**
     * Run test prepare method
     *
     * @param string $name
     * @param bool $showsTime
     * @param array $filterData
     * @param array|null $expectedCondition
     * @dataProvider getPrepareDataProvider
     * @return void
     */
    public function testPrepare(string $name, bool $showsTime, array $filterData, ?array $expectedCondition)
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects(static::atLeastOnce())->method('getProcessor')->willReturn($processor);
        /** @var FormDate|MockObject $uiComponent */
        $uiComponent = $this->getMockBuilder(FormDate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $uiComponent->expects($this->any())
            ->method('getContext')
            ->willReturn($this->contextMock);

        $this->contextMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn(Date::NAME);
        $this->contextMock->expects($this->any())
            ->method('addComponentDefinition')
            ->with(Date::NAME, ['extends' => Date::NAME]);

        $this->contextMock->expects($this->any())
            ->method('getFiltersParams')
            ->willReturn($filterData);

        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($this->dataProviderMock);

        if ($expectedCondition !== null) {
            $this->processFilters($name, $showsTime, $filterData, $expectedCondition, $uiComponent);
        }

        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->with($name, Date::COMPONENT, ['context' => $this->contextMock])
            ->willReturn($uiComponent);

        $date = new Date(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            [
                'name' => $name,
                'config' => ['options' => ['showsTime' => $showsTime]],
            ]
        );
        $date->prepare();
    }

    /**
     * Gets Filter mock
     *
     * @param string $name
     * @param string $expectedType
     * @param string $expectedDate
     * @param int $i
     *
     * @return Filter|MockObject
     */
    private function getFilterMock($name, $expectedType, $expectedDate, &$i)
    {
        $this->filterBuilderMock->expects(static::at($i++))
            ->method('setConditionType')
            ->with($expectedType)
            ->willReturnSelf();
        $this->filterBuilderMock->expects(static::at($i++))
            ->method('setField')
            ->with($name)
            ->willReturnSelf();
        $this->filterBuilderMock->expects(static::at($i++))
            ->method('setValue')
            ->with($expectedDate)
            ->willReturnSelf();

        $filterMock = $this->createMock(Filter::class);
        $this->filterBuilderMock->expects(static::at($i++))
            ->method('create')
            ->willReturn($filterMock);

        return $filterMock;
    }

    /**
     * @return array
     */
    public function getPrepareDataProvider()
    {
        return [
            [
                'name' => 'test_date',
                'showsTime' => false,
                'filterData' => ['test_date' => ['from' => '11-05-2015', 'to' => null]],
                'expectedCondition' => ['date' => '2015-05-11 00:00:00', 'type' => 'gteq'],
            ],
            [
                'name' => 'test_date',
                'showsTime' => false,
                'filterData' => ['test_date' => ['from' => null, 'to' => '11-05-2015']],
                'expectedCondition' => ['date' => '2015-05-11 23:59:59', 'type' => 'lteq'],
            ],
            [
                'name' => 'test_date',
                'showsTime' => false,
                'filterData' => ['test_date' => ['from' => '11-05-2015', 'to' => '11-05-2015']],
                'expectedCondition' => [
                    'date_from' => '2015-05-11 00:00:00', 'type_from' => 'gteq',
                    'date_to' => '2015-05-11 23:59:59', 'type_to' => 'lteq'
                ],
            ],
            [
                'name' => 'test_date',
                'showsTime' => false,
                'filterData' => ['test_date' => '11-05-2015'],
                'expectedCondition' => ['date' => '2015-05-11 00:00:00', 'type' => 'eq'],
            ],
            [
                'name' => 'test_date',
                'showsTime' => false,
                'filterData' => ['test_date' => ['from' => '', 'to' => '']],
                'expectedCondition' => null,
            ],
            [
                'name' => 'test_date',
                'showsTime' => true,
                'filterData' => ['test_date' => ['from' => '11-05-2015 10:20:00', 'to' => '11-05-2015 18:25:00']],
                'expectedCondition' => [
                    'date_from' => '2015-05-11 10:20:00', 'type_from' => 'gteq',
                    'date_to' => '2015-05-11 18:25:00', 'type_to' => 'lteq'
                ],
            ],
        ];
    }

    /**
     * @param string $name
     * @param bool $showsTime
     * @param array $filterData
     * @param array $expectedCondition
     * @param MockObject $uiComponent
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function processFilters(
        string $name,
        bool $showsTime,
        array $filterData,
        array $expectedCondition,
        FormDate $uiComponent
    ) {
        if (is_string($filterData[$name])) {
            $uiComponent->expects(static::once())
                ->method($showsTime ? 'convertDatetime' : 'convertDate')
                ->with($filterData[$name])
                ->willReturn(new \DateTime($filterData[$name]));
        } else {
            if ($showsTime) {
                $from = new \DateTime($filterData[$name]['from'] ?? 'now');
                $to = new \DateTime($filterData[$name]['to'] ?? 'now');
                $uiComponent->method('convertDatetime')
                    ->willReturnMap(
                        [
                            [$filterData[$name]['from'], true, $from],
                            [$filterData[$name]['to'], true, $to],
                        ]
                    );
            } else {
                $from = new \DateTime($filterData[$name]['from'] ?? 'now');
                $to = new \DateTime($filterData[$name]['to'] ? $filterData[$name]['to'] . ' 23:59:59' : 'now');
                $uiComponent->method('convertDate')
                    ->willReturnMap(
                        [
                            [$filterData[$name]['from'], 0, 0, 0, true, $from],
                            [$filterData[$name]['to'], 23, 59, 59, true, $to],
                        ]
                    );
            }
        }

        $i = 0;
        switch (true) {
            case is_string($filterData[$name]):
            case isset($filterData[$name]['from']) && !isset($filterData[$name]['to']):
            case !isset($filterData[$name]['from']) && isset($filterData[$name]['to']):
                $filterMock = $this->getFilterMock(
                    $name,
                    $expectedCondition['type'],
                    $expectedCondition['date'],
                    $i
                );
                $this->dataProviderMock->expects(static::once())
                    ->method('addFilter')
                    ->with($filterMock);
                break;
            case isset($filterData[$name]['from']) && isset($filterData[$name]['to']):
                $this->getFilterMock(
                    $name,
                    $expectedCondition['type_from'],
                    $expectedCondition['date_from'],
                    $i
                );
                $filterMock = $this->getFilterMock(
                    $name,
                    $expectedCondition['type_to'],
                    $expectedCondition['date_to'],
                    $i
                );
                $this->dataProviderMock->expects(static::exactly(2))
                    ->method('addFilter')
                    ->with($filterMock);
                break;
        }
    }
}

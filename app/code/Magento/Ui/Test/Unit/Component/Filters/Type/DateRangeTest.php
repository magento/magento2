<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface as UiContext;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\DateRange;
use Magento\Ui\Component\Form\Element\DataType\Date as FormDate;
use Magento\Ui\View\Element\BookmarkContextInterface;
use Magento\Ui\View\Element\BookmarkContextProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    /**
     * @var UiContext|MockObject
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
     * @var BookmarkContextInterface|MockObject
     */
    private $bookmarkContextMock;

    /**
     * @var BookmarkContextProviderInterface|MockObject
     */
    private $bookmarkContextProviderMock;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockForAbstractClass(
            UiContext::class,
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
        $this->bookmarkContextProviderMock = $this->getMockForAbstractClass(
            BookmarkContextProviderInterface::class
        );
        $this->bookmarkContextMock = $this->getMockForAbstractClass(
            BookmarkContextInterface::class
        );
        $this->bookmarkContextProviderMock->expects($this->once())
            ->method('getByUiContext')
            ->willReturn($this->bookmarkContextMock);
    }

    /**
     * Run test getComponentName method
     *
     * @return void
     */
    public function testGetComponentName()
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        $this->bookmarkContextMock->expects($this->once())
            ->method('getFilterData');
        $dateRange = new DateRange(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            [],
            $this->bookmarkContextProviderMock
        );
        $this->assertSame(DateRange::NAME, $dateRange->getComponentName());
    }

    /**
     * Run test prepare method
     *
     * @param string $name
     * @param array $filterData
     * @param array|null $expectedCondition
     * @dataProvider getPrepareDataProvider
     * @return void
     */
    public function testPrepare($name, $filterData, $expectedCondition)
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);

        /** @var FormDate $uiComponent */
        $uiComponent = $this->createMock(\Magento\Ui\Component\Form\Element\DataType\Date::class);

        $uiComponent->expects($this->any())
            ->method('getContext')
            ->willReturn($this->contextMock);

        $this->contextMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn(DateRange::NAME);
        $this->contextMock->expects($this->any())
            ->method('addComponentDefinition')
            ->with(DateRange::NAME, ['extends' => DateRange::NAME]);
        $dataProvider = $this->getMockForAbstractClass(
            DataProviderInterface::class
        );
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProvider);

        if ($expectedCondition !== null) {
            $filterMock = $this->createMock(Filter::class);
            $this->filterBuilderMock->expects($this->any())
                ->method('setConditionType')
                ->willReturnSelf();
            $this->filterBuilderMock->expects($this->any())
                ->method('setField')
                ->willReturnSelf();
            $this->filterBuilderMock->expects($this->any())
                ->method('setValue')
                ->willReturnSelf();
            $this->filterBuilderMock->expects($this->any())
                ->method('create')
                ->willReturn($filterMock);

            /** @var DataProviderInterface $dataProvider */
            $dataProvider->expects($this->any())
                ->method('addFilter')
                ->with($filterMock);

            $uiComponent->expects($this->any())
                ->method('getLocale')
                ->willReturn($expectedCondition['locale']);
            $uiComponent->expects($this->any())
                ->method('convertDate')
                ->willReturnCallback(function ($date) {
                    return new \DateTime($date);
                });
        }

        $this->bookmarkContextMock->expects($this->once())
            ->method('getFilterData')
            ->willReturn($filterData);
        $this->contextMock->expects($this->any())
            ->method('getRequestParam')
            ->with(UiContext::FILTER_VAR)
            ->willReturn($filterData);

        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->with($name, DateRange::COMPONENT, ['context' => $this->contextMock])
            ->willReturn($uiComponent);

        $dateRange = new DateRange(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            ['name' => $name],
            $this->bookmarkContextProviderMock
        );

        $dateRange->prepare();
    }

    /**
     * @return array
     */
    public function getPrepareDataProvider()
    {
        return [
            [
                'test_date',
                ['test_date' => ['from' => '11-05-2015', 'to' => '']],
                ['from' => '11-05-2015', 'orig_from' => '11-05-2015', 'datetime' => true, 'locale' => 'en_US'],
            ],
            [
                'test_date',
                ['test_date' => ['from' => '', 'to' => '11-05-2015']],
                ['to' => '11-05-2015', 'orig_to' => '11-05-2015', 'datetime' => true, 'locale' => 'en_US'],
            ],
            [
                'test_date',
                ['test_date' => ['from' => '10-05-2015', 'to' => '11-05-2015']],
                [
                    'from' => '10-05-2015',
                    'orig_from' => '10-05-2015',
                    'to' => '11-05-2015',
                    'orig_to' => '11-05-2015',
                    'datetime' => true,
                    'locale' => 'en_US'
                ],
            ],
            [
                'test_date',
                ['test_date' => ['from' => '', 'to' => '']],
                null,
            ],
        ];
    }
}

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
use Magento\Ui\Component\Filters\Type\Range;
use Magento\Ui\View\Element\BookmarkContextInterface;
use Magento\Ui\View\Element\BookmarkContextProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RangeTest extends TestCase
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
        $this->uiComponentFactory = $this->createMock(UiComponentFactory::class);
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
        $range = new Range(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            [],
            $this->bookmarkContextProviderMock
        );

        $this->assertSame(Range::NAME, $range->getComponentName());
    }

    /**
     * Run test prepare method
     *
     * @param string $name
     * @param array $filterData
     * @param array|null $expectedCalls
     * @dataProvider getPrepareDataProvider
     * @return void
     */
    public function testPrepare($name, $filterData, $expectedCalls)
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        $filter = $this->createMock(Filter::class);
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
            ->willReturn($filter);

        $this->contextMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn(Range::NAME);
        $this->contextMock->expects($this->any())
            ->method('addComponentDefinition')
            ->with(Range::NAME, ['extends' => Range::NAME]);

        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getMockForAbstractClass(
            DataProviderInterface::class,
            [],
            '',
            false
        );

        $this->contextMock->expects($this->atLeastOnce())
            ->method('getDataProvider')
            ->willReturn($dataProvider);

        $dataProvider->expects($this->exactly($expectedCalls))
            ->method('addFilter')
            ->with($filter);

        $this->bookmarkContextMock->expects($this->once())
            ->method('getFilterData')
            ->willReturn($filterData);
        $this->contextMock->expects($this->any())
            ->method('getRequestParam')
            ->with(UiContext::FILTER_VAR)
            ->willReturn($filterData);

        $range = new Range(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            ['name' => $name],
            $this->bookmarkContextProviderMock
        );
        $range->prepare();
    }

    /**
     * @return array
     */
    public function getPrepareDataProvider()
    {
        return [
            [
                'test_date',
                ['test_date' => ['from' => 0, 'to' => 1]],
                2
            ],
            [
                'test_date',
                ['test_date' => ['from' => '', 'to' => 2]],
                1
            ],
            [
                'test_date',
                ['test_date' => ['from' => 1, 'to' => '']],
                1
            ],
            [
                'test_date',
                ['test_date' => ['from' => 1, 'to' => 0]],
                2
            ],
            [
                'test_date',
                ['test_date' => ['from' => 1, 'to' => 2]],
                2
            ],
            [
                'test_date',
                ['test_date' => ['from' => 0, 'to' => 0]],
                2
            ],
            [
                'test_date',
                ['test_date' => ['from' => '0', 'to' => '0']],
                2
            ],
            [
                'test_date',
                ['test_date' => ['from' => '0.0', 'to' => 1]],
                2
            ],
            [
                'test_date',
                ['test_date' => ['from' => '', 'to' => '']],
                0
            ],
            [
                'test_date',
                ['test_date' => ['from' => 'a', 'to' => 'b']],
                0
            ],
            [
                'test_date',
                ['test_date' => ['from' => '1']],
                1
            ],
            [
                'test_date',
                ['test_date' => ['to' => '1']],
                1
            ],
            [
                'test_date',
                ['test_date' => []],
                0
            ],
        ];
    }
}

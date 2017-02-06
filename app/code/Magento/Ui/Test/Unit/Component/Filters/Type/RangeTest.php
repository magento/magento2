<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\View\Element\UiComponent\ContextInterface as UiContext;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\Type\Range;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class RangeTest
 */
class RangeTest extends \PHPUnit_Framework_TestCase
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
            \Magento\Framework\View\Element\UiComponent\ContextInterface::class,
            [],
            '',
            false
        );
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->getMock(
            \Magento\Framework\View\Element\UiComponentFactory::class,
            [],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(
            \Magento\Framework\Api\FilterBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->filterModifierMock = $this->getMock(
            \Magento\Ui\Component\Filters\FilterModifier::class,
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
        $range = new Range(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            []
        );

        $this->assertTrue($range->getComponentName() === Range::NAME);
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
        $this->contextMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn(Range::NAME);
        $this->contextMock->expects($this->any())
            ->method('addComponentDefinition')
            ->with(Range::NAME, ['extends' => Range::NAME]);
        $this->contextMock->expects($this->any())
            ->method('getRequestParam')
            ->with(UiContext::FILTER_VAR)
            ->willReturn($filterData);
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface::class,
            [],
            '',
            false
        );
        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProvider);
        if ($expectedCondition !== null) {
            $dataProvider->expects($this->any())
                ->method('addFilter')
                ->with($expectedCondition, $name);
        }

        $range = new Range(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            ['name' => $name]
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
                ['from' => null, 'orig_from' => 0, 'to' => 1],
            ],
            [
                'test_date',
                ['test_date' => ['from' => '', 'to' => 2]],
                ['from' => null, 'orig_from' => '', 'to' => 2],
            ],
            [
                'test_date',
                ['test_date' => ['from' => 1, 'to' => '']],
                ['from' => 1, 'orig_to' => '', 'to' => null],
            ],
            [
                'test_date',
                ['test_date' => ['from' => 1, 'to' => 0]],
                ['from' => 1, 'orig_to' => 0, 'to' => null],
            ],
            [
                'test_date',
                ['test_date' => ['from' => 1, 'to' => 2]],
                ['from' => 1, 'to' => 2],
            ],
            [
                'test_date',
                ['test_date' => ['from' => '', 'to' => '']],
                null,
            ],
            [
                'test_date',
                ['test_date' => []],
                null,
            ],
        ];
    }
}

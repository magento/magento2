<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Filters;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Class DateRangeTest
 */
class FilterModifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var DataProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProvider;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Ui\Component\Filters\FilterModifier
     */
    protected $unit;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class);
        $this->dataProvider = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface::class
        );
        $this->filterBuilder = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->unit = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Ui\Component\Filters\FilterModifier::class,
                [
                    'request' => $this->request,
                    'filterBuilder' => $this->filterBuilder,
                ]
            );
    }

    /**
     * @return void
     */
    public function testNotApplyFilterModifier()
    {
        $this->request->expects($this->once())->method('getParam')
            ->with(\Magento\Ui\Component\Filters\FilterModifier::FILTER_MODIFIER)
            ->willReturn([]);
        $this->dataProvider->expects($this->never())->method('addFilter');
        $this->unit->applyFilterModifier($this->dataProvider, 'test');
    }

    /**
     * @return void
     * @assertException \Magento\Framework\Exception\LocalizedException
     */
    public function testApplyFilterModifierWithNotAllowedCondition()
    {
        $this->request->expects($this->once())->method('getParam')
            ->with(\Magento\Ui\Component\Filters\FilterModifier::FILTER_MODIFIER)
            ->willReturn([
                'filter' => [
                    'condition_type' => 'not_allowed'
                ]
            ]);
        $this->dataProvider->expects($this->never())->method('addFilter');
        $this->unit->applyFilterModifier($this->dataProvider, 'test');
    }

    /**
     * @param $filterModifier
     * @param $filterName
     * @param $conditionType
     * @param $value
     * @return void
     * @dataProvider getApplyFilterModifierDataProvider
     */
    public function testApplyFilterModifierWith($filterModifier, $filterName, $conditionType, $value)
    {
        $filter = $this->createMock(\Magento\Framework\Api\Filter::class);

        $this->request->expects($this->once())->method('getParam')
            ->with(\Magento\Ui\Component\Filters\FilterModifier::FILTER_MODIFIER)
            ->willReturn($filterModifier);
        $this->filterBuilder->expects($this->once())->method('setConditionType')->with($conditionType)
            ->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setField')->with($filterName)->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setValue')->with($value)->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('create')->with()->willReturn($filter);
        $this->dataProvider->expects($this->once())->method('addFilter')->with($filter);

        $this->unit->applyFilterModifier($this->dataProvider, $filterName);
    }

    /**
     * @return array
     */
    public function getApplyFilterModifierDataProvider()
    {
        return [
            [
                [
                    'filter1' => ['condition_type' => 'eq', 'value' => '5',]
                ],
                'filter1',
                'eq',
                '5'
            ],
            [
                [
                    'filter2' => ['condition_type' => 'notnull']
                ],
                'filter2',
                'notnull',
                null
            ],
        ];
    }
}

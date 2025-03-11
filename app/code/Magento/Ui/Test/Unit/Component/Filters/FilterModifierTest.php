<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Filters;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\Component\Filters\FilterModifier;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FilterModifierTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var DataProviderInterface|MockObject
     */
    protected $dataProvider;

    /**
     * @var FilterBuilder|MockObject
     */
    protected $filterBuilder;

    /**
     * @var FilterModifier
     */
    protected $unit;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->dataProvider = $this->getMockForAbstractClass(
            DataProviderInterface::class
        );
        $this->filterBuilder = $this->createMock(FilterBuilder::class);
        $this->unit = (new ObjectManager($this))
            ->getObject(
                FilterModifier::class,
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
            ->with(FilterModifier::FILTER_MODIFIER)
            ->willReturn([]);
        $this->dataProvider->expects($this->never())->method('addFilter');
        $this->unit->applyFilterModifier($this->dataProvider, 'test');
    }

    /**
     * @return void
     */
    public function testApplyFilterModifierWithNotAllowedCondition()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Condition type "not_allowed" is not allowed');
        $this->request->expects($this->once())->method('getParam')
            ->with(FilterModifier::FILTER_MODIFIER)
            ->willReturn([
                'filter' => [
                    'condition_type' => 'not_allowed'
                ]
            ]);
        $this->dataProvider->expects($this->never())->method('addFilter');
        $this->unit->applyFilterModifier($this->dataProvider, 'filter');
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
        $filter = $this->createMock(Filter::class);

        $this->request->expects($this->once())->method('getParam')
            ->with(FilterModifier::FILTER_MODIFIER)
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
    public static function getApplyFilterModifierDataProvider()
    {
        return [
            [
                [
                    'filter1' => ['condition_type' => 'eq', 'value' => '5']
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

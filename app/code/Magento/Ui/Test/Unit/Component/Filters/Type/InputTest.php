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
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Input;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
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
     * @inheritdoc
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
    public function testGetComponentName(): void
    {
        $this->contextMock->expects($this->never())->method('getProcessor');
        $date = new Input(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            []
        );

        $this->assertSame(Input::NAME, $date->getComponentName());
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
    public function testPrepare(array $data, array $filterData, ?array $expectedCondition): void
    {
        $processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
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
            ->willReturn(Input::NAME);
        $this->contextMock->expects($this->any())
            ->method('addComponentDefinition')
            ->with(Input::NAME, ['extends' => Input::NAME]);
        $this->contextMock->expects($this->any())
            ->method('getFiltersParams')
            ->willReturn($filterData);
        $dataProvider = $this->getMockForAbstractClass(
            DataProviderInterface::class,
            [],
            '',
            false
        );

        $this->contextMock->expects($this->any())
            ->method('getDataProvider')
            ->willReturn($dataProvider);

        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->with($data['name'], Input::COMPONENT, ['context' => $this->contextMock])
            ->willReturn($uiComponent);

        if ($expectedCondition !== null) {
            $this->filterBuilderMock->expects($this->once())
                ->method('setConditionType')
                ->with($expectedCondition['setConditionType'])
                ->willReturnSelf();

            $this->filterBuilderMock->expects($this->once())
                ->method('setField')
                ->with($data['name'])
                ->willReturnSelf();

            $this->filterBuilderMock->expects($this->once())
                ->method('setValue')
                ->with($expectedCondition['setValue'])
                ->willReturnSelf();

            $filterMock = $this->getMockBuilder(Filter::class)
                ->disableOriginalConstructor()
                ->getMock();

            $this->filterBuilderMock->expects($this->once())
                ->method('create')
                ->willReturn($filterMock);
        }

        $date = new Input(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            $data
        );

        $date->prepare();
    }

    /**
     * @return array
     */
    public static function getPrepareDataProvider(): array
    {
        return [
            [
                [
                    'name' => 'test_date',
                ],
                ['test_date' => ''],
                null,
            ],
            [
                [
                    'name' => 'test_date',
                ],
                ['test_date' => null],
                null,
            ],
            [
                [
                    'name' => 'test_date',
                ],
                ['test_date' => '0'],
                [
                    'setConditionType' => 'like',
                    'setValue' => '%0%',
                ],
            ],
            [
                [
                    'name' => 'test_date',
                ],
                ['test_date' => 'some_value'],
                [
                    'setConditionType' => 'like',
                    'setValue' => '%some\_value%',
                ],
            ],
            [
                [
                    'name' => 'test_date',
                ],
                ['test_date' => '%'],
                [
                    'setConditionType' => 'like',
                    'setValue' => '%\%%',
                ],
            ],
            [
                [
                    'name' => 'text_attr',
                    'config' => [
                        'filter' => [
                            'filterType' => 'text',
                            'conditionType' => 'eq',
                        ]
                    ]
                ],
                ['text_attr' => 'something'],
                [
                    'setConditionType' => 'eq',
                    'setValue' => 'something',
                ],
            ],
            [
                [
                    'name' => 'text_attr',
                    'config' => [
                        'filter' => [
                            'filterType' => 'text',
                            'conditionType' => 'like',
                        ]
                    ]
                ],
                ['text_attr' => 'something'],
                [
                    'setConditionType' => 'like',
                    'setValue' => '%something%',
                ],
            ],
            [
                [
                    'name' => 'text_attr',
                    'config' => [
                        'filter' => [
                            'filterType' => 'text',
                            'conditionType' => 'like',
                            'valueExpression' => '%s%%'
                        ]
                    ]
                ],
                ['text_attr' => 'something'],
                [
                    'setConditionType' => 'like',
                    'setValue' => 'something%',
                ],
            ],
        ];
    }
}

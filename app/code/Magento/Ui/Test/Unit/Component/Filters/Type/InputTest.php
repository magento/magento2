<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface as UiContext;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters\Type\Input;

/**
 * Class InputTest
 */
class InputTest extends \PHPUnit\Framework\TestCase
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
        $this->uiComponentFactory = $this->createPartialMock(
            \Magento\Framework\View\Element\UiComponentFactory::class,
            ['create']
        );
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->filterModifierMock = $this->createPartialMock(
            \Magento\Ui\Component\Filters\FilterModifier::class,
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
        $date = new Input(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            []
        );

        $this->assertTrue($date->getComponentName() === Input::NAME);
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
        $processor = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->atLeastOnce())->method('getProcessor')->willReturn($processor);
        /** @var UiComponentInterface $uiComponent */
        $uiComponent = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\UiComponentInterface::class,
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
            ->method('getRequestParam')
            ->with(UiContext::FILTER_VAR)
            ->willReturn($filterData);
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

        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->with($name, Input::COMPONENT, ['context' => $this->contextMock])
            ->willReturn($uiComponent);

        $date = new Input(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            [],
            ['name' => $name]
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
                'test_date',
                ['test_date' => ''],
                null,
            ],
            [
                'test_date',
                ['test_date' => 'some_value'],
                ['like' => '%some_value%'],
            ],
        ];
    }
}

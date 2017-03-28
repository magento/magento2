<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\View\Element\UiComponent\ContextInterface as UiContext;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\Type\Input;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class InputTest
 */
class InputTest extends \PHPUnit_Framework_TestCase
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
            ['create'],
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

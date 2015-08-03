<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\Type\AbstractFilter;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class SelectTest
 */
class SelectTest extends \PHPUnit_Framework_TestCase
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
     * Set up
     */
    public function setUp()
    {
        $this->contextMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );

        $this->uiComponentFactory = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
            ['create'],
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
        $date = new Select($this->contextMock, $this->uiComponentFactory, null, []);

        $this->assertTrue($date->getComponentName() === Select::NAME);
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
            'Magento\Framework\View\Element\UiComponentInterface',
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
            ->method('getRequestParam')
            ->with(AbstractFilter::FILTER_VAR)
            ->willReturn($filterData);

        if ($expectedCondition !== null) {
            /** @var DataProviderInterface $dataProvider */
            $dataProvider = $this->getMockForAbstractClass(
                'Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface',
                [],
                '',
                false
            );
            $dataProvider->expects($this->any())
                ->method('addFilter')
                ->with($expectedCondition, $name);

            $this->contextMock->expects($this->any())
                ->method('getDataProvider')
                ->willReturn($dataProvider);
        }

        /** @var \Magento\Framework\Data\OptionSourceInterface $selectOptions */
        $selectOptions = $this->getMockForAbstractClass(
            'Magento\Framework\Data\OptionSourceInterface',
            [],
            '',
            false
        );

        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->with($name, Select::COMPONENT, ['context' => $this->contextMock, 'options' => $selectOptions])
            ->willReturn($uiComponent);

        $date = new Select($this->contextMock, $this->uiComponentFactory, $selectOptions, [], ['name' => $name]);

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
                ['eq' => 'some_value'],
            ],
        ];
    }
}

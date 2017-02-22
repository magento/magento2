<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\View\Element\UiComponent\ContextInterface as UiContext;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\Type\Date;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\DataType\Date as FormDate;

/**
 * Class DateTest
 */
class DateTest extends \PHPUnit_Framework_TestCase
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
    public function setUp()
    {
        $this->contextMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\ContextInterface',
            [],
            '',
            false
        );
        $processor = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\Processor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())->method('getProcessor')->willReturn($processor);
        $this->uiComponentFactory = $this->getMock(
            'Magento\Framework\View\Element\UiComponentFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->filterBuilderMock = $this->getMock(
            'Magento\Framework\Api\FilterBuilder',
            [],
            [],
            '',
            false
        );

        $this->filterModifierMock = $this->getMock(
            'Magento\Ui\Component\Filters\FilterModifier',
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
        $date = new Date(
            $this->contextMock,
            $this->uiComponentFactory,
            $this->filterBuilderMock,
            $this->filterModifierMock,
            []
        );

        $this->assertTrue($date->getComponentName() === Date::NAME);
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
        /** @var FormDate $uiComponent */
        $uiComponent = $this->getMock(
            'Magento\Ui\Component\Form\Element\DataType\Date',
            [],
            [],
            '',
            false
        );

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
            ->method('getRequestParam')
            ->with(UiContext::FILTER_VAR)
            ->willReturn($filterData);
        $dataProvider = $this->getMockForAbstractClass(
            'Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface',
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

            $uiComponent->expects($this->any())
                ->method('getLocale')
                ->willReturn($expectedCondition['locale']);
            $uiComponent->expects($this->any())
                ->method('convertDate')
                ->willReturnArgument(0);
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

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Filters\Type;

use Magento\Framework\View\Element\UiComponent\ContextInterface as UiContext;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\Type\DateRange;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Element\DataType\Date as FormDate;

/**
 * Class DateRangeTest
 */
class DateRangeTest extends \PHPUnit_Framework_TestCase
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
        $dateRange = new DateRange($this->contextMock, $this->uiComponentFactory, []);

        $this->assertTrue($dateRange->getComponentName() === DateRange::NAME);
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
            ->willReturn(DateRange::NAME);
        $this->contextMock->expects($this->any())
            ->method('addComponentDefinition')
            ->with(DateRange::NAME, ['extends' => DateRange::NAME]);
        $this->contextMock->expects($this->any())
            ->method('getRequestParam')
            ->with(UiContext::FILTER_VAR)
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

            $uiComponent->expects($this->any())
                ->method('getLocale')
                ->willReturn($expectedCondition['locale']);
            $uiComponent->expects($this->any())
                ->method('convertDate')
                ->willReturnArgument(0);
        }

        $this->uiComponentFactory->expects($this->any())
            ->method('create')
            ->with($name, DateRange::COMPONENT, ['context' => $this->contextMock])
            ->willReturn($uiComponent);

        $dateRange = new DateRange($this->contextMock, $this->uiComponentFactory, [], ['name' => $name]);

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

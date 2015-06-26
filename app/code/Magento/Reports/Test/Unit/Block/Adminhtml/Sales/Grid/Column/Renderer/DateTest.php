<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Sales\Grid\Column\Renderer;

use Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date
     */
    protected $date;

    /**
     * @var \Magento\Backend\Block\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolverMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->localeDate = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeDate
            ->expects($this->once())
            ->method('date')
            ->will($this->returnArgument(0));

        $this->contextMock = $this->getMockBuilder('Magento\Backend\Block\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock
            ->expects($this->once())
            ->method('getLocaleDate')
            ->will($this->returnValue($this->localeDate));

        $this->resolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->getMock();

        $this->date = new Date(
            $this->contextMock,
            $this->resolverMock
        );
    }

    /**
     * @param string $data
     * @param string $index
     * @param string $locale
     * @param string $period
     * @param string $result
     * @dataProvider datesDataProvider
     * @return void
     */
    public function testRender($data, $index, $locale, $period, $result)
    {
        $this->resolverMock->expects($this->any())->method('getLocale')->will($this->returnValue($locale));
        $this->localeDate->expects($this->any())->method('getDateFormat')->willReturnCallback(
            function ($value) use ($locale) {
                return (new \IntlDateFormatter(
                    $locale,
                    $value,
                    \IntlDateFormatter::NONE
                ))->getPattern();
            }
        );

        $objectMock = $this->getMockBuilder('Magento\Framework\Object')
            ->setMethods(['getData'])
            ->getMock();
        $objectMock->expects($this->once())->method('getData')->will($this->returnValue($data));

        $columnMock = $this->getMockBuilder('Magento\Backend\Block\Widget\Grid\Column')
            ->disableOriginalConstructor()
            ->setMethods(['getIndex', 'getPeriodType'])
            ->getMock();
        $columnMock->expects($this->once())->method('getIndex')->will($this->returnValue($index));
        $columnMock->expects($this->atLeastOnce())->method('getPeriodType')->will($this->returnValue($period));

        $this->date->setColumn($columnMock);

        $this->assertEquals($result, $this->date->render($objectMock));
    }

    /**
     * @return array
     */
    public function datesDataProvider()
    {
        return [
            [
                'data' => '2000',
                'index' => 'period',
                'locale' => 'en_US',
                'period' => 'year',
                'result' => '2000'
            ],
            [
                'data' => '2030',
                'index' => 'period',
                'locale' => 'en_US',
                'period' => 'year',
                'result' => '2030'
            ],
            [
                'data' => '2000-01',
                'index' => 'period',
                'locale' => 'en_US',
                'period' => 'month',
                'result' => '1/2000'
            ],
            [
                'data' => '2030-12',
                'index' => 'period',
                'locale' => 'en_US',
                'period' => 'month',
                'result' => '12/2030'
            ],
            [
                'data' => '2014-06-25',
                'index' => 'period',
                'locale' => 'en_US',
                'period' => 'day',
                'result' => 'Jun 25, 2014'
            ]
        ];
    }
}

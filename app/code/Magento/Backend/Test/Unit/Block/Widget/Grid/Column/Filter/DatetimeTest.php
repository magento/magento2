<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Filter;

/**
 * Class DateTimeTest to test Magento\Backend\Block\Widget\Grid\Column\Filter\Date
 *
 */
class DatetimeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Backend\Block\Widget\Grid\Column\Filter\Datetime */
    protected $model;

    /** @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject */
    protected $mathRandomMock;

    /** @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeResolverMock;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeFormatterMock;

    /** @var \Magento\Backend\Block\Widget\Grid\Column|\PHPUnit_Framework_MockObject_MockObject */
    protected $columnMock;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeDateMock;

    protected function setUp()
    {
        $this->mathRandomMock = $this->getMockBuilder(\Magento\Framework\Math\Random::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUniqueHash'])
            ->getMock();

        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->dateTimeFormatterMock = $this
            ->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->columnMock = $this->getMockBuilder(\Magento\Backend\Block\Widget\Grid\Column::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTimezone', 'getHtmlId', 'getId'])
            ->getMock();

        $this->localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Backend\Block\Widget\Grid\Column\Filter\Datetime::class,
            [
                'mathRandom' => $this->mathRandomMock,
                'localeResolver' => $this->localeResolverMock,
                'dateTimeFormatter' => $this->dateTimeFormatterMock,
                'localeDate' => $this->localeDateMock
            ]
        );
        $this->model->setColumn($this->columnMock);
    }

    public function testGetHtmlSuccessfulTimestamp()
    {
        $uniqueHash = 'H@$H';
        $id = 3;
        $format = 'mm/dd/yyyy';
        $yesterday = new \DateTime();
        $yesterday->add(\DateInterval::createFromDateString('yesterday'));
        $tomorrow = new \DateTime();
        $tomorrow->add(\DateInterval::createFromDateString('tomorrow'));
        $value = [
            'locale' => 'en_US',
            'from' => $yesterday->getTimestamp(),
            'to' => $tomorrow->getTimestamp()
        ];

        $this->mathRandomMock->expects($this->any())->method('getUniqueHash')->willReturn($uniqueHash);
        $this->columnMock->expects($this->once())->method('getHtmlId')->willReturn($id);
        $this->localeDateMock->expects($this->any())->method('getDateFormat')->willReturn($format);
        $this->columnMock->expects($this->any())->method('getTimezone')->willReturn(false);
        $this->localeResolverMock->expects($this->any())->method('getLocale')->willReturn('en_US');
        $this->model->setColumn($this->columnMock);
        $this->model->setValue($value);

        $output = $this->model->getHtml();
        $this->assertContains('id="' . $uniqueHash . '_from" value="' . $yesterday->getTimestamp(), $output);
        $this->assertContains('id="' . $uniqueHash . '_to" value="' . $tomorrow->getTimestamp(), $output);
    }
}

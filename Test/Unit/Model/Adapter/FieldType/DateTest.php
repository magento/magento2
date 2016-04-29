<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldType;

use Magento\Elasticsearch\Model\Adapter\FieldType\Date as DateField;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DateField
     */
    private $model;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTime;

    /**
     * @var TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDate;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEmptyDate'])
            ->getMock();

        $this->localeDate = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldType\Date::class,
            [
                'dateTime' => $this->dateTime,
                'localeDate' => $this->localeDate,
                'scopeConfig' => $this->scopeConfig
            ]
        );
    }

    /**
     * Test formatDate() method
     */
    public function testFormatDateEmpty()
    {
        $this->dateTime->expects($this->once())
            ->method('isEmptyDate')
            ->willReturn(true);
        $this->assertNull($this->model->formatDate(1, null));
    }

    /**
     * Test formatDate() method
     */
    public function testFormatDate()
    {
        $this->dateTime->expects($this->once())
            ->method('isEmptyDate')
            ->willReturn(false);
        $this->localeDate->expects($this->once())
            ->method('getDefaultTimezonePath')
            ->willReturn('timezonePath');
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn('Europe/Kiev');
        $this->model->formatDate(1, '1997-12-31');
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\Html;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\View\Element\Html\Calendar */
    protected $block;

    /** @var \Magento\Framework\View\Element\Template\Context */
    protected $context;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $localeDate;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->localeDate = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->getMock();

        /** @var  \Magento\Framework\View\Element\Template\Context $context */
        $this->context = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Template\Context',
            [
                'localeDate' => $this->localeDate,
            ]
        );

        /** @var \Magento\Framework\View\Element\Html\Links $block */
        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Html\Calendar',
            ['context' => $this->context]
        );
    }

    /**
     * @test
     */
    public function testGetYearRange()
    {
        $testCurrentYear = 2123;
        $date = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\DateInterface')
            ->getMock();

        $date->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue($testCurrentYear));

        $this->localeDate->expects($this->any())
            ->method('date')
            ->with($this->equalTo('Y'))
            ->will($this->returnValue($date));

        $this->assertEquals((int)$testCurrentYear - 100 . ':' . $testCurrentYear, $this->block->getYearRange());
    }
}

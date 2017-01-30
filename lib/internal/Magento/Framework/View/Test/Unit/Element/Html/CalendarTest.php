<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\Html;

class CalendarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
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
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
        $testCurrentYear = (new \DateTime())->format('Y');
        $this->assertEquals((int)$testCurrentYear - 100 . ':' . ($testCurrentYear + 100), $this->block->getYearRange());
    }
}

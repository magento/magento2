<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array(
                'localeDate' => $this->localeDate,
            )
        );

        /** @var \Magento\Framework\View\Element\Html\Links $block */
        $this->block = $this->objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Html\Calendar',
            array('context' => $this->context)
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

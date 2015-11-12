<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Block\Order\Create;

/**
 * Class TotalsTest
 */
class TotalsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Create\Totals
     */
    protected $totals;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $helperManager;

    /**
     * @var \Magento\Backend\Model\Session\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionQuoteMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * Init
     */
    protected function setUp()
    {
        $this->helperManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sessionQuoteMock = $this->getMockBuilder('Magento\Backend\Model\Session\Quote')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods([
                'setTotalsCollectedFlag',
                'collectTotals',
                'getTotals'
            ])
            ->getMock();
        $this->sessionQuoteMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);
        $this->totals = $this->helperManager->getObject(
            'Magento\Sales\Block\Adminhtml\Order\Create\Totals', ['sessionQuote' => $this->sessionQuoteMock]
        );
    }

    public function testGetTotals()
    {
        $expected = 'expected';
        $this->quoteMock->expects($this->at(0))->method('setTotalsCollectedFlag')->with(false);
        $this->quoteMock->expects($this->at(1))->method('collectTotals');
        $this->quoteMock->expects($this->at(2))->method('getTotals')->willReturn($expected);
        $this->assertEquals($expected, $this->totals->getTotals());
    }
}

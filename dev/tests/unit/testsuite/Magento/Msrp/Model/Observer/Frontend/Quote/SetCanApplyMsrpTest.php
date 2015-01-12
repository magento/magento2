<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model\Observer\Frontend\Quote;

use Magento\Sales\Model\Quote\Address;
use Magento\TestFramework\Helper\ObjectManager;

/**
 * Tests Magento\Msrp\Model\Observer\Frontend\Quote\SetCanApplyMsrp
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetCanApplyMsrpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Msrp\Model\Observer\Frontend\Quote\SetCanApplyMsrp
     */
    protected $observer;

    /**
     * @var \Magento\Msrp\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder('Magento\Msrp\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->observer = (new ObjectManager($this))->getObject(
            'Magento\Msrp\Model\Observer\Frontend\Quote\SetCanApplyMsrp',
            ['config' => $this->configMock]
        );
    }

    /**
     * @param bool $isMsrpEnabled
     * @param bool $canApplyMsrp
     * @dataProvider setQuoteCanApplyMsrpDataProvider
     */
    public function testSetQuoteCanApplyMsrp($isMsrpEnabled, $canApplyMsrp)
    {
        $eventMock = $this->getMockBuilder('Magento\Framework\Event')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote'])
            ->getMock();
        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'setCanApplyMsrp', 'getAllAddresses'])
            ->getMock();
        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($eventMock));
        $eventMock->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($quoteMock));
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue($isMsrpEnabled));
        $quoteMock->expects($this->once())
            ->method('setCanApplyMsrp')
            ->with($canApplyMsrp);
        $addressMock1 = $this->getMockBuilder('Magento\Customer\Model\Address\AbstractAddress')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMockForAbstractClass();
        $addressMock1->setCanApplyMsrp($canApplyMsrp);
        $addressMock2 = $this->getMockBuilder('Magento\Customer\Model\Address\AbstractAddress')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMockForAbstractClass();
        $addressMock2->setCanApplyMsrp(false);
        $quoteMock->expects($this->any())
            ->method('getAllAddresses')
            ->will($this->returnValue([$addressMock1, $addressMock2]));
        $this->observer->execute($observerMock);
    }

    public function setQuoteCanApplyMsrpDataProvider()
    {
        return [
            [false, false],
            [true, true],
            [true, false]
        ];
    }
}

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
namespace Magento\Msrp\Model\Observer\Frontend\Quote;

use Magento\Framework\Object;
use Magento\TestFramework\Helper\ObjectManager;
use Magento\Sales\Model\Quote\Address;

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

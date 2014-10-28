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
namespace Magento\RecurringPayment\Model\Observer;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class PaymentAvailabilityObserverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\RecurringPayment\Model\Observer\PaymentAvailabilityObserver */
    protected $paymentAvailabilityObserver;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\RecurringPayment\Model\Quote\Filter|\PHPUnit_Framework_MockObject_MockObject */
    protected $filterMock;

    protected function setUp()
    {
        $this->filterMock = $this->getMock('Magento\RecurringPayment\Model\Quote\Filter');

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->paymentAvailabilityObserver = $this->objectManagerHelper->getObject(
            'Magento\RecurringPayment\Model\Observer\PaymentAvailabilityObserver',
            array('quoteFilter' => $this->filterMock)
        );
    }

    public function testObserve()
    {
        $quote = $this->getMockBuilder('Magento\Sales\Model\Quote')->disableOriginalConstructor()->getMock();

        $event = new \Magento\Framework\Event(
            array(
                'quote' => $quote,
                'method_instance' => $this->getMockBuilder(
                    'Magento\Payment\Model\Method\AbstractMethod'
                )->disableOriginalConstructor()->getMock(),
                'result' => new \StdClass()
            )
        );
        $this->filterMock->expects(
            $this->once()
        )->method(
            'hasRecurringItems'
        )->with(
            $quote
        )->will(
            $this->returnValue(true)
        );

        $observer = $this->getMockBuilder('Magento\Framework\Event\Observer')->disableOriginalConstructor()->getMock();

        $observer->expects($this->any())->method('getEvent')->will($this->returnValue($event));

        $this->paymentAvailabilityObserver->observe($observer);
        $this->assertFalse($event->getResult()->isAvailable);
    }
}

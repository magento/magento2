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

namespace Magento\Paypal\Model;
use Magento\TestFramework\Matcher\MethodInvokedAtIndex as MethodInvokedAtIndex;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
     */
    protected $_model;

    /**
     * @var \Magento\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Object
     */
    protected $_event;

    /**
     * @var \Magento\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_authorization;

    /**
     * @var \Magento\Paypal\Model\Billing\Agreement Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_agreementFactory;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_checkoutSession;

    protected function setUp()
    {
        $this->_event = new \Magento\Object();

        $this->_observer = new \Magento\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->_authorization = $this->getMockForAbstractClass('Magento\AuthorizationInterface');
        $this->_agreementFactory = $this->getMock(
            'Magento\Paypal\Model\Billing\AgreementFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_checkoutSession = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Paypal\Model\Observer',
            [
                'authorization' => $this->_authorization,
                'agreementFactory' => $this->_agreementFactory,
                'checkoutSession' => $this->_checkoutSession
            ]
        );
    }

    public function testAddPaypalShortcuts()
    {
        $layoutMock = $this->getMockBuilder('Magento\Core\Model\Layout')
            ->setMethods(array('createBlock'))
            ->disableOriginalConstructor()
            ->getMock();
        $blocks = array(
            'Magento\Paypal\Block\Express\Shortcut',
            'Magento\Paypal\Block\PayflowExpress\Shortcut'
        );

        $blockInstances = array();
        foreach ($blocks as $atPosition => $blockName) {
            $block = $this->getMockBuilder($blockName)
                ->setMethods(null)
                ->disableOriginalConstructor()
                ->getMock();

            $blockInstances[$blockName] = $block;

            $layoutMock->expects(new MethodInvokedAtIndex($atPosition))
                ->method('createBlock')
                ->with($blockName)
                ->will($this->returnValue($block));
        }

        $shortcutButtonsMock = $this->getMockBuilder('Magento\Catalog\Block\ShortcutButtons')
            ->setMethods(array('getLayout', 'addShortcut'))
            ->disableOriginalConstructor()
            ->getMock();

        $shortcutButtonsMock->expects($this->any())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));

        foreach ($blocks as $atPosition => $blockName) {
            $shortcutButtonsMock->expects(new MethodInvokedAtIndex($atPosition))
                ->method('addShortcut')
                ->with($this->identicalTo($blockInstances[$blockName]));
        }

        $this->_event->setContainer($shortcutButtonsMock);
        $this->_model->addPaypalShortcuts($this->_observer);
    }

    /**
     * @param object $methodInstance
     * @param bool $isAllowed
     * @param bool $isAvailable
     * @dataProvider restrictAdminBillingAgreementUsageDataProvider
     */
    public function testRestrictAdminBillingAgreementUsage($methodInstance, $isAllowed, $isAvailable)
    {
        $this->_event->setMethodInstance($methodInstance);
        $this->_authorization->expects($this->any())
            ->method('isAllowed')
            ->with('Magento_Paypal::use')
            ->will($this->returnValue($isAllowed));
        $result = new \stdClass();
        $result->isAvailable = true;
        $this->_event->setResult($result);
        $this->_model->restrictAdminBillingAgreementUsage($this->_observer);
        $this->assertEquals($isAvailable, $result->isAvailable);
    }

    public function restrictAdminBillingAgreementUsageDataProvider()
    {
        return [
            [new \stdClass(), false, true],
            [
                $this->getMockForAbstractClass(
                    'Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement',
                    [],
                    '',
                    false
                ),
                true,
                true
            ],
            [
                $this->getMockForAbstractClass(
                    'Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement',
                    [],
                    '',
                    false
                ),
                false,
                false
            ],
        ];
    }

    public function testAddBillingAgreementToSessionNoData()
    {
        $payment = $this->getMock('Magento\Sales\Model\Order\Payment', [], [], '', false);
        $payment->expects($this->once())
            ->method('__call')
            ->with('getBillingAgreementData')
            ->will($this->returnValue(null));
        $this->_event->setPayment($payment);
        $this->_agreementFactory->expects($this->never())->method('create');
        $this->_checkoutSession->expects($this->once())
            ->method('__call')
            ->with('unsLastBillingAgreementReferenceId');
        $this->_model->addBillingAgreementToSession($this->_observer);
    }

    /**
     * @param bool $isValid
     * @dataProvider addBillingAgreementToSessionDataProvider
     */
    public function testAddBillingAgreementToSession($isValid)
    {
        $agreement = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $agreement->expects($this->once())->method('isValid')->will($this->returnValue($isValid));
        $comment = $this->getMockForAbstractClass(
            'Magento\Core\Model\AbstractModel',
            [],
            '',
            false,
            true,
            true,
            ['__wakeup']
        );
        $order = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $order->expects($this->once())
            ->method('addStatusHistoryComment')
            ->with(
                $isValid
                    ? __('Created billing agreement #%1.', 'agreement reference id')
                    : __('We couldn\'t create a billing agreement for this order.')
            )
            ->will($this->returnValue($comment));
        if ($isValid) {
            $agreement->expects($this->any())
                ->method('__call')
                ->with('getReferenceId')
                ->will($this->returnValue('agreement reference id'));
            $order->expects(new MethodInvokedAtIndex(0))
                ->method('addRelatedObject')
                ->with($agreement);
            $this->_checkoutSession->expects($this->once())
                ->method('__call')
                ->with('setLastBillingAgreementReferenceId', ['agreement reference id']);
        } else {
            $this->_checkoutSession->expects($this->once())
                ->method('__call')
                ->with('unsLastBillingAgreementReferenceId');
            $agreement->expects($this->never())
                ->method('__call');
        }
        $order->expects(new MethodInvokedAtIndex($isValid ? 1 : 0))
            ->method('addRelatedObject')
            ->with($comment);

        $payment = $this->getMock('Magento\Sales\Model\Order\Payment', [], [], '', false);
        $payment->expects($this->once())
            ->method('__call')
            ->with('getBillingAgreementData')
            ->will($this->returnValue('not empty'));
        $payment->expects($this->once())->method('getOrder')->will($this->returnValue($order));
        $agreement->expects($this->once())
            ->method('importOrderPayment')
            ->with($payment)
            ->will($this->returnValue($agreement));
        $this->_event->setPayment($payment);
        $this->_agreementFactory->expects($this->once())->method('create')->will($this->returnValue($agreement));
        $this->_model->addBillingAgreementToSession($this->_observer);
    }

    public function addBillingAgreementToSessionDataProvider()
    {
        return [[true], [false]];
    }
}

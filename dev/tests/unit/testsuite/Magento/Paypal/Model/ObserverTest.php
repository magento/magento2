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

use Magento\TestFramework\Matcher\MethodInvokedAtIndex;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Observer
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Framework\Object
     */
    protected $_event;

    /**
     * @var \Magento\Framework\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $this->_event = new \Magento\Framework\Object();

        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->_authorization = $this->getMockForAbstractClass('Magento\Framework\AuthorizationInterface');
        $this->_agreementFactory = $this->getMock(
            'Magento\Paypal\Model\Billing\AgreementFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_checkoutSession = $this->getMock('Magento\Checkout\Model\Session', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Paypal\Model\Observer',
            array(
                'authorization' => $this->_authorization,
                'agreementFactory' => $this->_agreementFactory,
                'checkoutSession' => $this->_checkoutSession
            )
        );
    }

    public function testAddPaypalShortcuts()
    {
        $layoutMock = $this->getMockBuilder(
            'Magento\Framework\View\Layout'
        )->setMethods(
            array('createBlock')
        )->disableOriginalConstructor()->getMock();
        $blocks = [
            'Magento\Paypal\Block\Express\Shortcut' => 'Magento\Paypal\Block\Express\Shortcut',
            'Magento\Paypal\Block\PayflowExpress\Shortcut' => 'Magento\Paypal\Block\Express\Shortcut',
            'Magento\Paypal\Block\Bml\Shortcut' => 'Magento\Paypal\Block\Bml\Shortcut',
            'Magento\Paypal\Block\Payflow\Bml\Shortcut' => 'Magento\Paypal\Block\Bml\Shortcut'
        ];

        $blockInstances = array();
        $atPosition = 0;
        foreach ($blocks as $blockName => $blockInstance) {
            $block = $this->getMockBuilder($blockInstance)->setMethods(null)->disableOriginalConstructor()->getMock();

            $blockInstances[$blockName] = $block;

            $layoutMock->expects(new MethodInvokedAtIndex($atPosition))->method('createBlock')->with($blockName)
                ->will($this->returnValue($block));
            $atPosition++;
        }

        $shortcutButtonsMock = $this->getMockBuilder(
            'Magento\Catalog\Block\ShortcutButtons'
        )->setMethods(
            array('getLayout', 'addShortcut')
        )->disableOriginalConstructor()->getMock();

        $shortcutButtonsMock->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));

        $atPosition = 0;
        foreach (array_keys($blocks) as $blockName) {
            $shortcutButtonsMock->expects(new MethodInvokedAtIndex($atPosition))->method('addShortcut')
                ->with($this->identicalTo($blockInstances[$blockName]));
            $atPosition++;
        }
        $this->_event->setContainer($shortcutButtonsMock);
        $this->_model->addPaypalShortcuts($this->_observer);

        foreach ($blockInstances as $instance) {
            $this->assertEquals(\Magento\Paypal\Model\Observer::SHORTCUT_TEMPLATE, $instance->getTemplate());
        }
    }

    public function testAddBillingAgreementToSessionNoData()
    {
        $payment = $this->getMock('Magento\Sales\Model\Order\Payment', array(), array(), '', false);
        $payment->expects(
            $this->once()
        )->method(
            '__call'
        )->with(
            'getBillingAgreementData'
        )->will(
            $this->returnValue(null)
        );
        $this->_event->setPayment($payment);
        $this->_agreementFactory->expects($this->never())->method('create');
        $this->_checkoutSession->expects($this->once())->method('__call')->with('unsLastBillingAgreementReferenceId');
        $this->_model->addBillingAgreementToSession($this->_observer);
    }

    /**
     * @param bool $isValid
     * @dataProvider addBillingAgreementToSessionDataProvider
     */
    public function testAddBillingAgreementToSession($isValid)
    {
        $agreement = $this->getMock('Magento\Paypal\Model\Billing\Agreement', array(), array(), '', false);
        $agreement->expects($this->once())->method('isValid')->will($this->returnValue($isValid));
        $comment = $this->getMockForAbstractClass(
            'Magento\Framework\Model\AbstractModel',
            [],
            '',
            false,
            true,
            true,
            array('__wakeup')
        );
        $order = $this->getMock('Magento\Sales\Model\Order', array(), array(), '', false);
        $order->expects(
            $this->once()
        )->method(
            'addStatusHistoryComment'
        )->with(
            $isValid ? __(
                'Created billing agreement #%1.',
                'agreement reference id'
            ) : __(
                'We couldn\'t create a billing agreement for this order.'
            )
        )->will(
            $this->returnValue($comment)
        );
        if ($isValid) {
            $agreement->expects(
                $this->any()
            )->method(
                '__call'
            )->with(
                'getReferenceId'
            )->will(
                $this->returnValue('agreement reference id')
            );
            $order->expects(new MethodInvokedAtIndex(0))->method('addRelatedObject')->with($agreement);
            $this->_checkoutSession->expects(
                $this->once()
            )->method(
                '__call'
            )->with(
                'setLastBillingAgreementReferenceId',
                array('agreement reference id')
            );
        } else {
            $this->_checkoutSession->expects(
                $this->once()
            )->method(
                '__call'
            )->with(
                'unsLastBillingAgreementReferenceId'
            );
            $agreement->expects($this->never())->method('__call');
        }
        $order->expects(new MethodInvokedAtIndex($isValid ? 1 : 0))->method('addRelatedObject')->with($comment);

        $payment = $this->getMock('Magento\Sales\Model\Order\Payment', array(), array(), '', false);
        $payment->expects(
            $this->once()
        )->method(
            '__call'
        )->with(
            'getBillingAgreementData'
        )->will(
            $this->returnValue('not empty')
        );
        $payment->expects($this->once())->method('getOrder')->will($this->returnValue($order));
        $agreement->expects(
            $this->once()
        )->method(
            'importOrderPayment'
        )->with(
            $payment
        )->will(
            $this->returnValue($agreement)
        );
        $this->_event->setPayment($payment);
        $this->_agreementFactory->expects($this->once())->method('create')->will($this->returnValue($agreement));
        $this->_model->addBillingAgreementToSession($this->_observer);
    }

    public function addBillingAgreementToSessionDataProvider()
    {
        return array(array(true), array(false));
    }
}

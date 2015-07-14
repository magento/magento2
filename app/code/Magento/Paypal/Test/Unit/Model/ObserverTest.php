<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Matcher\MethodInvokedAtIndex;

/**
 * Class ObserverTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Paypal\Model\Observer
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

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \Magento\Paypal\Helper\Hss|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalHssMock;

    /**
     * @var \Magento\Paypal\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalDataMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paypalConfigMock;

    protected function setUp()
    {
        $this->_event = new \Magento\Framework\Object();

        $this->_observer = new \Magento\Framework\Event\Observer();
        $this->_observer->setEvent($this->_event);

        $this->_authorization = $this->getMockForAbstractClass('Magento\Framework\AuthorizationInterface');
        $this->_agreementFactory = $this->getMock(
            'Magento\Paypal\Model\Billing\AgreementFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->_checkoutSession = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->coreRegistryMock = $this->getMock(
            'Magento\Framework\Registry',
            [],
            [],
            '',
            false
        );
        $this->paypalHssMock = $this->getMock(
            'Magento\Paypal\Helper\Hss',
            ['getHssMethods'],
            [],
            '',
            false
        );
        $this->paypalDataMock = $this->getMock(
            '\Magento\Paypal\Helper\Data',
            ['getHtmlTransactionId'],
            [],
            '',
            false
        );
        $this->viewMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\ViewInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->paypalConfigMock = $this->getMock(
            'Magento\Paypal\Model\Config',
            [],
            [],
            '',
            false
        );
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Paypal\Model\Observer',
            [
                'authorization' => $this->_authorization,
                'agreementFactory' => $this->_agreementFactory,
                'checkoutSession' => $this->_checkoutSession,
                'coreRegistry' => $this->coreRegistryMock,
                'paypalHss' => $this->paypalHssMock,
                'view' => $this->viewMock,
                'paypalConfig' => $this->paypalConfigMock,
                'paypalData' => $this->paypalDataMock
            ]
        );
    }

    /**
     * Get data for test testSetResponseAfterSaveOrderSuccess
     *
     * @return array
     */
    protected function getSetResponseAfterSaveOrderTestData()
    {
        $iFrameHtml = 'iframe-html';
        $paymentMethod = 'method-2';

        return [
            'order.getId' => 10,
            'payment.getMethod' => $paymentMethod,
            'paypalHss.getHssMethods' => [
                'method-1',
                $paymentMethod,
                'method-3'
            ],
            'result.getData' => [
                'error' => false
            ],
            'block.toHtml' => $iFrameHtml,
            'result.setData' => [
                'error' => false,
                'update_section' => [
                    'name' => 'paypaliframe',
                    'html' => $iFrameHtml
                ],
                'redirect' => false,
                'success' => false,
            ]
        ];
    }

    /**
     * Run setResponseAfterSaveOrder method test
     *
     * @return void
     */
    public function testSetResponseAfterSaveOrderSuccess()
    {
        $testData = $this->getSetResponseAfterSaveOrderTestData();

        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock = $this->getMockBuilder('Magento\Sales\Model\Order')
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Payment')
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\BlockInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('hss_order')
            ->willReturn($orderMock);

        $orderMock->expects($this->once())
            ->method('getId')
            ->willReturn($testData['order.getId']);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($testData['payment.getMethod']);

        $this->paypalHssMock->expects($this->once())
            ->method('getHssMethods')
            ->willReturn($testData['paypalHss.getHssMethods']);

        $observerMock->expects($this->atLeastOnce())
            ->method('getData')
            ->with('result')
            ->willReturn($resultMock);

        $resultMock->expects($this->once())
            ->method('getData')
            ->willReturn($testData['result.getData']);

        $this->viewMock->expects($this->once())
            ->method('loadLayout')
            ->with('checkout_onepage_review', true, true, false);
        $this->viewMock->expects($this->once())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('paypal.iframe')
            ->willReturn($blockMock);

        $blockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($testData['block.toHtml']);

        $resultMock->expects($this->once())
            ->method('setData')
            ->with($testData['result.setData']);

        $this->_model->setResponseAfterSaveOrder($observerMock);
    }

    /**
     * @param array $paymentMethodsAvailability
     * @param array $blocks
     * @dataProvider addAvailabilityOfMethodsDataProvider
     */
    public function testAddPaypalShortcuts($paymentMethodsAvailability, $blocks)
    {
        $this->paypalConfigMock->expects($this->any())
            ->method('isMethodAvailable')
            ->will($this->returnValueMap($paymentMethodsAvailability));

        $layoutMock = $this->getMockBuilder(
            'Magento\Framework\View\Layout'
        )->setMethods(
            ['createBlock']
        )->disableOriginalConstructor()->getMock();

        $shortcutButtonsMock = $this->getMockBuilder(
            'Magento\Catalog\Block\ShortcutButtons'
        )->setMethods(
            ['getLayout', 'addShortcut']
        )->disableOriginalConstructor()->getMock();

        $blockInstances = [];
        $atPosition = 0;
        foreach ($blocks as $blockName => $blockInstance) {
            if ($this->paypalConfigMock->isMethodAvailable($blockInstance[1])) {
                $block = $this->getMockBuilder($blockInstance[0])
                    ->setMethods(null)
                    ->disableOriginalConstructor()
                    ->getMock();
                $blockInstances[$blockName] = $block;
                $layoutMock->expects(new MethodInvokedAtIndex($atPosition))->method('createBlock')->with($blockName)
                    ->will($this->returnValue($block));
                $atPosition++;
            }
        }
        $shortcutButtonsMock->expects($this->any())->method('getLayout')->will($this->returnValue($layoutMock));
        $atPosition = 0;
        foreach ($blocks as $blockName => $blockInstance) {
            if ($this->paypalConfigMock->isMethodAvailable($blockInstance[1])) {
                $shortcutButtonsMock->expects(new MethodInvokedAtIndex($atPosition))->method('addShortcut')
                    ->with($this->identicalTo($blockInstances[$blockName]));
                $atPosition++;
            }
        }
        $this->_event->setContainer($shortcutButtonsMock);
        $this->_model->addPaypalShortcuts($this->_observer);
    }

    public function testAddBillingAgreementToSessionNoData()
    {
        $payment = $this->getMock('Magento\Sales\Model\Order\Payment', [], [], '', false);
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
        $agreement = $this->getMock('Magento\Paypal\Model\Billing\Agreement', [], [], '', false);
        $agreement->expects($this->once())->method('isValid')->will($this->returnValue($isValid));
        $comment = $this->getMockForAbstractClass(
            'Magento\Framework\Model\AbstractModel',
            [],
            '',
            false,
            true,
            true,
            ['__wakeup']
        );
        $order = $this->getMock('Magento\Sales\Model\Order', [], [], '', false);
        $order->expects(
            $this->once()
        )->method(
            'addStatusHistoryComment'
        )->with(
            $isValid ? __(
                'Created billing agreement #%1.',
                'agreement reference id'
            ) : __(
                'We can\'t create a billing agreement for this order.'
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
                ['agreement reference id']
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

        $payment = $this->getMock('Magento\Sales\Model\Order\Payment', [], [], '', false);
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
        return [[true], [false]];
    }

    public function testObserveHtmlTransactionId()
    {
        $observerMock = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->setMethods(['getDataObject'])
            ->disableOriginalConstructor()
            ->getMock();
        $transactionMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment\Transaction')
            ->setMethods(['getOrder', 'getTxnId', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock = $this->getMockBuilder('\Magento\Sales\Model\Order')
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMock = $this->getMockBuilder('\Magento\Sales\Model\Order\Payment')
            ->setMethods(['getMethodInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $methodInstanceMock = $this->getMockBuilder('\Magento\Payment\Model\MethodInterface')
            ->setMethods(['getCode'])
            ->getMockForAbstractClass();


        $observerMock->expects($this->once())
            ->method('getDataObject')
            ->willReturn($transactionMock);
        $transactionMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($paymentMock);
        $paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstanceMock);
        $methodInstanceMock->expects($this->once())
            ->method('getCode')
            ->willReturn("'test'");
        $transactionMock->expects($this->once())
            ->method('getTxnId')
            ->willReturn("'test'");

        $this->paypalDataMock->expects($this->once())
            ->method('getHtmlTransactionId')
            ->willReturn('test');

        $transactionMock->expects($this->once())
            ->method('setData')->with('html_txn_id', 'test');


        $this->_model->observeHtmlTransactionId($observerMock);
    }

    public function addAvailabilityOfMethodsDataProvider()
    {
        $blocks = [
            'Magento\Paypal\Block\Express\ShortcutContainer' =>
                ['Magento\Paypal\Block\Express\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS],
            'Magento\Paypal\Block\Express\Shortcut' =>
                ['Magento\Paypal\Block\Express\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS],
            'Magento\Paypal\Block\PayflowExpress\Shortcut' =>
                ['Magento\Paypal\Block\Express\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS],
            'Magento\Paypal\Block\PayflowExpress\ShortcutContainer' =>
                ['Magento\Paypal\Block\Express\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS],
            'Magento\Paypal\Block\Bml\Shortcut' =>
                ['Magento\Paypal\Block\Bml\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS],
            'Magento\Paypal\Block\Payflow\Bml\Shortcut' =>
                ['Magento\Paypal\Block\Bml\Shortcut', \Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS],
        ];

        $allMethodsAvailable = [
            [\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS, true],
            [\Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS, true]
        ];

        $allMethodsNotAvailable = [
            [\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS, false],
            [\Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS, false]
        ];

        $firstMethodAvailable = [
            [\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS, true],
            [\Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS, false]
        ];

        $secondMethodAvailable = [
            [\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS, false],
            [\Magento\Paypal\Model\Config::METHOD_WPP_PE_EXPRESS, true]
        ];

        return [
            [$allMethodsAvailable, $blocks],
            [$allMethodsNotAvailable, $blocks],
            [$firstMethodAvailable, $blocks],
            [$secondMethodAvailable, $blocks]
        ];
    }
}

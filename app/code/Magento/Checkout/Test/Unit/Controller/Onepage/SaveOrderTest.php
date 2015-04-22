<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Onepage;

use Magento\Checkout\Controller\Onepage\SaveOrder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SaveOrderTest
 */
class SaveOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveOrder
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidatorMock;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRawFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $contextMock = $this->getMock(
            'Magento\Framework\App\Action\Context',
            [],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['getPost']
        );
        $this->responseMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\ResponseInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->objectManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\ObjectManagerInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->formKeyValidatorMock = $this->getMock(
            'Magento\Framework\Data\Form\FormKey\Validator',
            [],
            [],
            '',
            false
        );
        $this->resultRedirectFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RedirectFactory',
            [],
            [],
            '',
            false
        );
        $this->resultRawFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\RawFactory',
            [],
            [],
            '',
            false
        );
        $this->resultJsonFactoryMock = $this->getMock(
            'Magento\Framework\Controller\Result\JsonFactory',
            [],
            [],
            '',
            false
        );
        $this->eventManagerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Event\ManagerInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );

        $contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $contextMock->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->resultRedirectFactoryMock);
        $contextMock->expects($this->once())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->controller = $helper->getObject(
            'Magento\Checkout\Controller\Onepage\SaveOrder',
            [
                'context' => $contextMock,
                'formKeyValidator' => $this->formKeyValidatorMock,
                'resultRawFactory' => $this->resultRawFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
            ]
        );
    }

    /**
     * Test for execute method
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $data = [
            'payment-key-1' => 'payment-value-1',
            'checks' => [
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL,
            ]
        ];

        $onepageMock = $this->getMock(
            'Magento\Checkout\Model\Type\Onepage',
            [],
            [],
            '',
            false
        );
        $redirectMock = $this->getMock(
            'Magento\Framework\Controller\Result\Redirect',
            [],
            [],
            '',
            false
        );
        $quoteMock = $this->getMock(
            'Magento\Quote\Model\Quote',
            [],
            [],
            '',
            false
        );
        $agreementsValidatorMock = $this->getMock(
            'Magento\Checkout\Model\Agreements\AgreementsValidator',
            [],
            [],
            '',
            false
        );
        $paymentMock = $this->getMock(
            'Magento\Quote\Model\Quote\Payment',
            [],
            [],
            '',
            false
        );
        $checkoutMock = $this->getMock(
            'Magento\Checkout\Model\Session',
            ['getRedirectUrl'],
            [],
            '',
            false
        );
        $resultJsonMock = $this->getMock(
            'Magento\Framework\Controller\Result\Json',
            [],
            [],
            '',
            false
        );

        $redirectMock->expects($this->never())
            ->method('setPath')
            ->with('*/*/')
            ->willReturn('redirect');

        $this->formKeyValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->resultRedirectFactoryMock->expects($this->never())
            ->method('create')
            ->willReturn($redirectMock);

        $this->objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap(
                [
                    ['Magento\Checkout\Model\Type\Onepage', $onepageMock],
                    ['Magento\Checkout\Model\Agreements\AgreementsValidator', $agreementsValidatorMock],
                ]
            );

        // call _expireAjax method
        $onepageMock->expects($this->atLeastOnce())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('hasItems')
            ->willReturn(false);
        $quoteMock->expects($this->never())
            ->method('getHasError')
            ->willReturn(true);
        $quoteMock->expects($this->never())
            ->method('validateMinimumAmount')
            ->willReturn(false);

        $this->requestMock->expects($this->never())
            ->method('getActionName');
        // -- end

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getPost')
            ->willReturnMap(
                [
                    [
                        'agreement',
                        [],
                        [
                            'test-key-1' => 'test-value-1'
                        ]
                    ],
                    [
                        'payment',
                        [],
                        $data
                    ],
                ]
            );

        $agreementsValidatorMock->expects($this->once())
            ->method('isValid')
            ->with(['test-key-1'])
            ->willReturn(true);

        $quoteMock->expects($this->atLeastOnce())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $paymentMock->expects($this->once())
            ->method('setQuote')
            ->with($quoteMock);
        $paymentMock->expects($this->once())
            ->method('importData')
            ->with($data);

        $onepageMock->expects($this->once())
            ->method('saveOrder');
        $onepageMock->expects($this->once())
            ->method('getCheckout')
            ->willReturn($checkoutMock);

        $checkoutMock->expects($this->once())
            ->method('getRedirectUrl')
            ->willReturn(null);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->withConsecutive(
                $this->equalTo('checkout_controller_onepage_saveOrder'),
                $this->countOf(2)
            );

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($resultJsonMock);

        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(['success' => 1, 'error' => false])
            ->willReturnSelf();

        $this->assertEquals($resultJsonMock, $this->controller->execute());
    }
}

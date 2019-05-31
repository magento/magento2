<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Model\Payflow;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Payment\Model\Method\ConfigInterface as PaymentConfigInterface;
use Magento\Payment\Model\Method\ConfigInterfaceFactory as PaymentConfigInterfaceFactory;
use Magento\Paypal\Model\Cart as PayPalCart;
use Magento\Paypal\Model\CartFactory as PayPalCartFactory;
use Magento\Paypal\Model\Payflow\Service\Gateway as PayPalPayflowGateway;
use Magento\Paypal\Model\Payflow\Transparent as PayPalPayflowTransparent;
use Magento\Paypal\Model\Payflowpro;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory as PaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransparentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PayPalPayflowTransparent
     */
    private $subject;

    /**
     * @var PaymentConfigInterface|MockObject
     */
    private $paymentConfig;

    /**
     * @var PayPalPayflowGateway|MockObject
     */
    private $payPalPayflowGateway;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    private $paymentToken;

    /**
     * @var PayPalCart|MockObject
     */
    private $payPalCart;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var Order|MockObject
     */
    private $order;

    public function setUp()
    {
        $this->initPayment();

        $this->subject = (new ObjectManagerHelper($this))
            ->getObject(
                PayPalPayflowTransparent::class,
                [
                    'configFactory' => $this->getPaymentConfigInterfaceFactory(),
                    'paymentExtensionFactory' => $this->getPaymentExtensionInterfaceFactory(),
                    'storeManager' => $this->getStoreManager(),
                    'gateway' => $this->getPayPalPayflowGateway(),
                    'paymentTokenFactory' => $this->getPaymentTokenFactory(),
                    'payPalCartFactory' => $this->getPayPalCartFactory(),
                    'scopeConfig' => $this->getScopeConfig(),
                ]
            );
    }

    /**
     * Asserts that authorize request to Payflow gateway is valid.
     *
     * @dataProvider validAuthorizeRequestDataProvider
     * @param DataObject $validAuthorizeRequest
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException
     */
    public function testValidAuthorizeRequest(DataObject $validAuthorizeRequest)
    {
        $this->scopeConfig->method('getValue')
            ->willReturnMap(
                [
                    ['payment/payflowpro/user', ScopeInterface::SCOPE_STORE, null, 'user'],
                    ['payment/payflowpro/vendor', ScopeInterface::SCOPE_STORE, null, 'vendor'],
                    ['payment/payflowpro/partner', ScopeInterface::SCOPE_STORE, null, 'partner'],
                    ['payment/payflowpro/pwd', ScopeInterface::SCOPE_STORE, null, 'pwd'],
                    ['payment/payflowpro/verbosity', ScopeInterface::SCOPE_STORE, null, 'verbosity'],
                ]
            );
        $this->paymentConfig->method('getBuildNotationCode')->willReturn('BUTTONSOURCE');
        $this->payment->method('getAdditionalInformation')
            ->willReturnMap(
                [
                    [Payflowpro::PNREF, 'XXXXXXXXXXXX'],
                ]
            );
        $this->order->method('getIncrementId')->willReturn('000000001');
        $this->order->method('getBaseCurrencyCode')->willReturn('USD');
        $this->payPalCart->method('getSubtotal')->willReturn(5.00);
        $this->payPalCart->method('getTax')->willReturn(5.00);
        $this->payPalCart->method('getShipping')->willReturn(5.00);
        $this->payPalCart->method('getDiscount')->willReturn(5.00);

        $this->payPalPayflowGateway->expects($this->once())
            ->method('postRequest')
            ->with($this->equalTo($validAuthorizeRequest));

        $this->subject->authorize($this->payment, 10);
    }

    /**
     * @return array
     */
    public function validAuthorizeRequestDataProvider(): array
    {
        return [
            [
                new DataObject(
                    [
                        'user' => 'user',
                        'vendor' => 'vendor',
                        'partner' => 'partner',
                        'pwd' => 'pwd',
                        'verbosity' => 'verbosity',
                        'BUTTONSOURCE' => 'BUTTONSOURCE',
                        'tender' => 'C',
                        'custref' => '000000001',
                        'invnum' => '000000001',
                        'comment1' => '000000001',
                        'trxtype' => 'A',
                        'origid' => 'XXXXXXXXXXXX',
                        'amt' => '10.00',
                        'currency' => 'USD',
                        'itemamt' => '5.00',
                        'taxamt' => '5.00',
                        'freightamt' => '5.00',
                        'discount' => '5.00',
                    ]
                ),
            ]
        ];
    }

    /**
     * @return PaymentConfigInterfaceFactory|MockObject
     */
    private function getPaymentConfigInterfaceFactory()
    {
        $paymentConfigInterfaceFactory = $this->getMockBuilder(PaymentConfigInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentConfig = $this->getMockBuilder(PaymentConfigInterface::class)
            ->setMethods(['setStoreId', 'setMethodInstance', 'setMethod', 'getBuildNotationCode'])
            ->getMockForAbstractClass();

        $paymentConfigInterfaceFactory->method('create')->willReturn($this->paymentConfig);

        return $paymentConfigInterfaceFactory;
    }

    /**
     * @return PaymentExtensionInterfaceFactory|MockObject
     */
    private function getPaymentExtensionInterfaceFactory()
    {
        $paymentExtensionInterfaceFactory = $this->getMockBuilder(PaymentExtensionInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderPaymentExtension = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['setVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentExtensionInterfaceFactory->method('create')->willReturn($orderPaymentExtension);

        return $paymentExtensionInterfaceFactory;
    }

    /**
     * @return StoreManagerInterface|MockObject
     */
    private function getStoreManager()
    {
        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $store = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();

        $storeManager->method('getStore')->willReturn($store);

        return $storeManager;
    }

    /**
     * @return PayPalPayflowGateway|MockObject
     */
    private function getPayPalPayflowGateway()
    {
        $this->payPalPayflowGateway = $this->getMockBuilder(PayPalPayflowGateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payPalPayflowGateway->method('postRequest')
            ->willReturn(new DataObject());

        return $this->payPalPayflowGateway;
    }

    /**
     * @return PaymentTokenInterfaceFactory|MockObject
     */
    private function getPaymentTokenFactory()
    {
        $paymentTokenInterfaceFactory = $this->getMockBuilder(PaymentTokenInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentToken = $this->getMockBuilder(PaymentTokenInterface::class)
            ->getMockForAbstractClass();

        $paymentTokenInterfaceFactory->method('create')->willReturn($this->paymentToken);

        return $paymentTokenInterfaceFactory;
    }

    /**
     * @return PayPalCartFactory|MockObject
     */
    private function getPayPalCartFactory()
    {
        $payPalCartFactory = $this->getMockBuilder(PayPalCartFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->payPalCart = $this->getMockBuilder(PayPalCart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payPalCartFactory->method('create')->willReturn($this->payPalCart);

        return $payPalCartFactory;
    }

    /**
     * @return ScopeConfigInterface|MockObject
     */
    private function getScopeConfig()
    {
        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        return $this->scopeConfig;
    }

    /**
     * @return Payment|MockObject
     */
    private function initPayment()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->method('getOrder')->willReturn($this->order);
        $this->payment->method('setTransactionId')->willReturnSelf();
        $this->payment->method('setIsTransactionClosed')->willReturnSelf();
        $this->payment->method('getCcExpYear')->willReturn('2019');
        $this->payment->method('getCcExpMonth')->willReturn('05');

        return $this->payment;
    }
}

<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestPaymentInformationManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartManagementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteIdMaskFactoryMock;

    /**
     * @var \Magento\Checkout\Model\GuestPaymentInformationManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->billingAddressManagementMock = $this->getMock(
            \Magento\Quote\Api\GuestBillingAddressManagementInterface::class
        );
        $this->paymentMethodManagementMock = $this->getMock(
            \Magento\Quote\Api\GuestPaymentMethodManagementInterface::class
        );
        $this->cartManagementMock = $this->getMock(\Magento\Quote\Api\GuestCartManagementInterface::class);
        $this->cartRepositoryMock = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);

        $this->quoteIdMaskFactoryMock = $this->getMock(
            \Magento\Quote\Model\QuoteIdMaskFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            \Magento\Checkout\Model\GuestPaymentInformationManagement::class,
            [
                'billingAddressManagement' => $this->billingAddressManagementMock,
                'paymentMethodManagement' => $this->paymentMethodManagementMock,
                'cartManagement' => $this->cartManagementMock,
                'cartRepository' => $this->cartRepositoryMock,
                'quoteIdMaskFactory' => $this->quoteIdMaskFactoryMock,
                'connectionPull' => $this->resourceConnectionMock
            ]
        );
        $objectManager->setBackwardCompatibleProperty($this->model, 'logger', $this->loggerMock);
    }

    public function testSavePaymentInformationAndPlaceOrder()
    {
        $cartId = 100;
        $orderId = 200;
        $email = 'email@magento.com';
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $adapterMockForSales = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $adapterMockForCheckout = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceConnectionMock->expects($this->at(0))
            ->method('getConnection')
            ->with('sales')
            ->willReturn($adapterMockForSales);
        $adapterMockForSales->expects($this->once())->method('beginTransaction');
        $adapterMockForSales->expects($this->once())->method('commit');

        $this->resourceConnectionMock->expects($this->at(1))
            ->method('getConnection')
            ->with('checkout')
            ->willReturn($adapterMockForCheckout);
        $adapterMockForCheckout->expects($this->once())->method('beginTransaction');
        $adapterMockForCheckout->expects($this->once())->method('commit');

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->with($cartId)->willReturn($orderId);

        $this->assertEquals(
            $orderId,
            $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock)
        );
    }

    /**
     * @expectedExceptionMessage An error occurred on the server. Please try to place the order again.
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSavePaymentInformationAndPlaceOrderException()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $adapterMockForSales = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $adapterMockForCheckout = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceConnectionMock->expects($this->at(0))
            ->method('getConnection')
            ->with('sales')
            ->willReturn($adapterMockForSales);
        $adapterMockForSales->expects($this->once())->method('beginTransaction');
        $adapterMockForSales->expects($this->once())->method('rollback');

        $this->resourceConnectionMock->expects($this->at(1))
            ->method('getConnection')
            ->with('checkout')
            ->willReturn($adapterMockForCheckout);
        $adapterMockForCheckout->expects($this->once())->method('beginTransaction');
        $adapterMockForCheckout->expects($this->once())->method('rollback');

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $exception = new \Exception(__('DB exception'));
        $this->loggerMock->expects($this->once())->method('critical');
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock);
    }

    public function testSavePaymentInformation()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);

        $this->assertTrue($this->model->savePaymentInformation($cartId, $email, $paymentMock, $billingAddressMock));
    }

    public function testSavePaymentInformationWithoutBillingAddress()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, [], [], '', false);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $this->billingAddressManagementMock->expects($this->never())->method('assign');
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $quoteIdMaskMock = $this->getMock('Magento\Quote\Model\QuoteIdMask', ['getQuoteId', 'load'], [], '', false);
        $this->quoteIdMaskFactoryMock->expects($this->once())->method('create')->willReturn($quoteIdMaskMock);
        $quoteIdMaskMock->expects($this->once())->method('load')->with($cartId, 'masked_id')->willReturnSelf();
        $quoteIdMaskMock->expects($this->once())->method('getQuoteId')->willReturn($cartId);
        $this->cartRepositoryMock->expects($this->once())->method('getActive')->with($cartId)->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getBillingAddress')->willReturn($billingAddressMock);
        $billingAddressMock->expects($this->once())->method('setEmail')->with($email);
        $this->assertTrue($this->model->savePaymentInformation($cartId, $email, $paymentMock));
    }

    /**
     * @expectedExceptionMessage DB exception
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     */
    public function testSavePaymentInformationAndPlaceOrderWithLocalizedException()
    {
        $cartId = 100;
        $email = 'email@magento.com';
        $paymentMock = $this->getMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $billingAddressMock = $this->getMock(\Magento\Quote\Api\Data\AddressInterface::class);

        $billingAddressMock->expects($this->once())->method('setEmail')->with($email)->willReturnSelf();

        $adapterMockForSales = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $adapterMockForCheckout = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceConnectionMock->expects($this->at(0))
            ->method('getConnection')
            ->with('sales')
            ->willReturn($adapterMockForSales);
        $adapterMockForSales->expects($this->once())->method('beginTransaction');
        $adapterMockForSales->expects($this->once())->method('rollback');

        $this->resourceConnectionMock->expects($this->at(1))
            ->method('getConnection')
            ->with('checkout')
            ->willReturn($adapterMockForCheckout);
        $adapterMockForCheckout->expects($this->once())->method('beginTransaction');
        $adapterMockForCheckout->expects($this->once())->method('rollback');

        $this->billingAddressManagementMock->expects($this->once())
            ->method('assign')
            ->with($cartId, $billingAddressMock);
        $this->paymentMethodManagementMock->expects($this->once())->method('set')->with($cartId, $paymentMock);
        $phrase = new \Magento\Framework\Phrase(__('DB exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->loggerMock->expects($this->never())->method('critical');
        $this->cartManagementMock->expects($this->once())->method('placeOrder')->willThrowException($exception);

        $this->model->savePaymentInformationAndPlaceOrder($cartId, $email, $paymentMock, $billingAddressMock);
    }
}

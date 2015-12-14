<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Observer;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Vault\Observer\AfterPaymentSaveObserver;

class AfterPaymentSaveObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PaymentTokenManagementInterface
     */
    private $paymentTokenManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EncryptorInterface
     */
    private $encryptor;

    /**
     * @var AfterPaymentSaveObserver
     */
    private $observer;

    protected function setUp()
    {
        $this->paymentTokenManager = $this->getMock(PaymentTokenManagementInterface::class);
        $this->encryptor = $this->getMock(EncryptorInterface::class);

        $this->observer = new AfterPaymentSaveObserver(
            $this->paymentTokenManager,
            $this->encryptor
        );
    }

    public function testExecutePositiveCase()
    {
        $expectedPublicHash = 'expected public hash';
        $isActive = true;
        $isVisible = true;

        $customerId = 1;
        $gatewayToken = 'gateway token';
        $paymentMethodCode = 'vault provider payment code';
        $hashInput = $customerId . $gatewayToken . $paymentMethodCode;
        $additionalInformation = [
            VaultConfigProvider::IS_ACTIVE_CODE => "1"
        ];

        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderPayment = $this->getMock(OrderPaymentInterface::class);
        $extensionAttributesI = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['getVaultPaymentToken', 'setVaultPaymentToken'])
            ->getMock();
        $paymentToken = $this->getMock(PaymentTokenInterface::class);

        $observer->expects(static::once())
            ->method('getDataByKey')
            ->with(AfterPaymentSaveObserver::PAYMENT_OBJECT_DATA_KEY)
            ->willReturn($orderPayment);
        $orderPayment->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesI);
        $extensionAttributesI->expects(static::once())
            ->method('getVaultPaymentToken')
            ->willReturn($paymentToken);

        $paymentToken->expects(static::atLeastOnce())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $paymentToken->expects(static::atLeastOnce())
            ->method('getGatewayToken')
            ->willReturn($gatewayToken);
        $paymentToken->expects(static::atLeastOnce())
            ->method('getPaymentMethodCode')
            ->willReturn($paymentMethodCode);

        $this->encryptor->expects(static::once())
            ->method('getHash')
            ->with($hashInput)
            ->willReturn($expectedPublicHash);

        $paymentToken->expects(static::once())
            ->method('setPublicHash')
            ->with($expectedPublicHash);
        $paymentToken->expects(static::once())
            ->method('setIsActive')
            ->with($isActive);

        $orderPayment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn($additionalInformation);

        $paymentToken->expects(static::once())
            ->method('setIsVisible')
            ->with(true);

        $this->paymentTokenManager->expects(static::once())
            ->method('saveTokenWithPaymentLink')
            ->with($paymentToken, $orderPayment);

        $extensionAttributesI->expects(static::once())
            ->method('setVaultPaymentToken')
            ->with($paymentToken);

        $this->observer->execute($observer);
    }

    public function testExecuteEmptyGatewayToken()
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $orderPayment = $this->getMock(OrderPaymentInterface::class);
        $extensionAttributesI = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['getVaultPaymentToken', 'setVaultPaymentToken'])
            ->getMock();
        $paymentToken = $this->getMock(PaymentTokenInterface::class);

        $observer->expects(static::once())
            ->method('getDataByKey')
            ->with(AfterPaymentSaveObserver::PAYMENT_OBJECT_DATA_KEY)
            ->willReturn($orderPayment);
        $orderPayment->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesI);
        $extensionAttributesI->expects(static::once())
            ->method('getVaultPaymentToken')
            ->willReturn($paymentToken);

        $paymentToken->expects(static::once())
            ->method('getGatewayToken')
            ->willReturn(null);

        $this->paymentTokenManager->expects(static::never())
            ->method('saveTokenWithPaymentLink')
            ->with($paymentToken, $orderPayment);

        $this->observer->execute($observer);
    }
}

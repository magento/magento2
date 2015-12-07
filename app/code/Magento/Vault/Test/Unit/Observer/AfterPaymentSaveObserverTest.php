<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Observer;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Math\Random;
use Magento\Sales\Api\Data\OrderPaymentExtension;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Model\PaymentToken;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Observer\AfterPaymentSaveObserver;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class AfterPaymentSaveObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer|MockObject
     */
    protected $eventObserverArgMock;

    /**
     * @var \Magento\Vault\Observer\AfterPaymentSaveObserver
     */
    protected $observer;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    protected $encryptorModel;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtension|MockObject paymentExtension
     */
    protected $paymentExtension;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionFactory|MockObject paymentExtensionFactoryMock
     */
    protected $paymentExtensionFactoryMock;

    /**
     * @var \Magento\Vault\Model\PaymentTokenManagement|MockObject paymentTokenManagementMock
     */
    protected $paymentTokenManagementMock;

    /**
     * @var \Magento\Vault\Model\PaymentTokenFactory|MockObject paymentTokenFactoryMock
     */
    protected $paymentTokenFactoryMock;

    /**
     * @var \Magento\Vault\Model\PaymentToken|MockObject paymentTokenMock
     */
    protected $paymentTokenMock;

    /**
     * @var \Magento\Sales\Model\Order|MockObject salesOrderMock
     */
    protected $salesOrderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject salesOrderPaymentMock
     */
    protected $salesOrderPaymentMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        /** @var Random|MockObject $encryptorRandomGenerator */
        $encryptorRandomGenerator = $this->getMock(Random::class, [], [], '', false);
        /** @var DeploymentConfig|MockObject $deploymentConfigMock */
        $deploymentConfigMock = $this->getMock(DeploymentConfig::class, [], [], '', false);
        $this->encryptorModel = new Encryptor($encryptorRandomGenerator, $deploymentConfigMock);

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtension::class)
            ->setMethods(['setVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtension->expects($this->any())
            ->method('setVaultPaymentToken')
            ->withAnyParameters();
        $this->paymentExtensionFactoryMock = $this
            ->getMockBuilder(OrderPaymentExtensionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->paymentExtension);

        $this->paymentTokenManagementMock = $this->getMockBuilder(PaymentTokenManagement::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentTokenMock = $this->getMockBuilder(PaymentToken::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactoryMock = $this->getMockBuilder(PaymentTokenFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentTokenFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->paymentTokenMock);

        // Sales Order Model
        $this->salesOrderMock = $this->getMock(Order::class, null, [], '', false);

        // Sales Order Payment Model
        $this->salesOrderPaymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['getTransactionAdditionalInfo'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesOrderPaymentMock->setOrder($this->salesOrderMock);

        // Arguments to observer container
        $this->eventObserverArgMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['getDataByKey'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventObserverArgMock->expects($this->any())
            ->method('getDataByKey')
            ->with(AfterPaymentSaveObserver::PAYMENT_OBJECT_DATA_KEY)
            ->willReturn($this->salesOrderPaymentMock);

        // Observer class
        $this->observer = new AfterPaymentSaveObserver(
            $this->paymentExtensionFactoryMock,
            $this->paymentTokenManagementMock,
            $this->paymentTokenFactoryMock,
            $this->encryptorModel
        );
    }

    /**
     * @param int $customerId
     * @param string $createdAt
     * @param string $token
     * @param bool $isActive
     * @param string $method
     * @dataProvider testPositiveCaseDataProvider
     */
    public function testPositiveCase($customerId, $createdAt, $token, $isActive, $method)
    {
        $this->salesOrderMock->setCustomerId($customerId);
        $this->salesOrderMock->setCreatedAt($createdAt);
        $this->salesOrderPaymentMock->setMethod($method);

        $this->salesOrderPaymentMock->expects($this->once())
            ->method('getTransactionAdditionalInfo')
            ->willReturn([AfterPaymentSaveObserver::TRANSACTION_CC_TOKEN_DATA_KEY => $token]);
        if (!empty($token)) {
            $this->paymentTokenManagementMock->expects($this->once())
                ->method('getByGatewayToken')
                ->willReturn(null);
            $this->paymentTokenManagementMock->expects($this->once())
                ->method('saveTokenWithPaymentLink')
                ->willReturn(true);

            $this->assertSame($this->observer, $this->observer->execute($this->eventObserverArgMock));

            $this->assertEquals($customerId, $this->paymentTokenMock->getCustomerId());
            $this->assertEquals($isActive, $this->paymentTokenMock->getIsActive());
            $this->assertEquals($createdAt, $this->paymentTokenMock->getCreatedAt());
        } else {
            $this->paymentTokenManagementMock->expects($this->never())
                ->method('getByGatewayToken');
            $this->paymentTokenManagementMock->expects($this->never())
                ->method('saveTokenWithPaymentLink');

            $this->assertSame($this->observer, $this->observer->execute($this->eventObserverArgMock));

            $this->assertEquals($customerId, $this->paymentTokenMock->getCustomerId());
            $this->assertEquals($isActive, $this->paymentTokenMock->getIsActive());
            $this->assertEquals($createdAt, $this->paymentTokenMock->getCreatedAt());
        }
    }

    public function testPositiveCaseDataProvider()
    {
        return [
            [
                1,
                '10\20\2015',
                'asdfg',
                true,
                'paypal'
            ],
            [
                null,
                null,
                null,
                false,
                null
            ],
        ];
    }
}

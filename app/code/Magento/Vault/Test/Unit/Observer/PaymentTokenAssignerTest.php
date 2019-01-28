<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Vault\Model\Method\Vault;
use Magento\Vault\Observer\PaymentTokenAssigner;

/**
 * Class PaymentTokenAssignerTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentTokenAssignerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentTokenManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenManagement;

    /**
     * @var PaymentTokenAssigner
     */
    private $observer;

    public function setUp()
    {
        $this->paymentTokenManagement = $this->createMock(PaymentTokenManagementInterface::class);
        $this->observer = new PaymentTokenAssigner($this->paymentTokenManagement);
    }

    public function testExecuteNoPublicHash()
    {
        $dataObject = new DataObject();
        $observer = $this->getPreparedObserverWithMap(
            [
                [AbstractDataAssignObserver::DATA_CODE, $dataObject]
            ]
        );

        $this->paymentTokenManagement->expects($this->never())
            ->method('getByPublicHash');
        $this->observer->execute($observer);
    }

    public function testExecuteNotOrderPaymentModel()
    {
        $dataObject = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    PaymentTokenInterface::PUBLIC_HASH => 'public_hash_value'
                ]
            ]
        );
        $paymentModel = $this->createMock(InfoInterface::class);

        $observer = $this->getPreparedObserverWithMap(
            [
                [AbstractDataAssignObserver::DATA_CODE, $dataObject],
                [AbstractDataAssignObserver::MODEL_CODE, $paymentModel]
            ]
        );

        $this->paymentTokenManagement->expects($this->never())
            ->method('getByPublicHash');
        $this->observer->execute($observer);
    }

    public function testExecuteNoPaymentToken()
    {
        $customerId = 1;
        $publicHash = 'public_hash_value';
        $dataObject = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    PaymentTokenInterface::PUBLIC_HASH => $publicHash
                ]
            ]
        );

        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->createMock(CartInterface::class);
        $customer = $this->createMock(CustomerInterface::class);

        $paymentModel->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);
        $quote->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);
        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->paymentTokenManagement->expects($this->once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn(null);

        $observer = $this->getPreparedObserverWithMap(
            [
                [AbstractDataAssignObserver::DATA_CODE, $dataObject],
                [AbstractDataAssignObserver::MODEL_CODE, $paymentModel]
            ]
        );

        $paymentModel->expects($this->never())
            ->method('setAdditionalInformation');

        $this->observer->execute($observer);
    }

    public function testExecuteSaveMetadata()
    {
        $customerId = 1;
        $publicHash = 'public_hash_value';
        $dataObject = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => [
                    PaymentTokenInterface::PUBLIC_HASH => $publicHash
                ]
            ]
        );

        $paymentModel = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quote = $this->createMock(CartInterface::class);
        $customer = $this->createMock(CustomerInterface::class);
        $paymentToken = $this->createMock(PaymentTokenInterface::class);

        $paymentModel->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);
        $quote->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);
        $customer->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);

        $this->paymentTokenManagement->expects($this->once())
            ->method('getByPublicHash')
            ->with($publicHash, $customerId)
            ->willReturn($paymentToken);

        $paymentModel->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(
                [
                    PaymentTokenInterface::CUSTOMER_ID => $customerId,
                    PaymentTokenInterface::PUBLIC_HASH => $publicHash
                ]
            );

        $observer = $this->getPreparedObserverWithMap(
            [
                [AbstractDataAssignObserver::DATA_CODE, $dataObject],
                [AbstractDataAssignObserver::MODEL_CODE, $paymentModel]
            ]
        );

        $this->observer->execute($observer);
    }

    /**
     * @param array $returnMap
     * @return \PHPUnit_Framework_MockObject_MockObject|Observer
     */
    private function getPreparedObserverWithMap(array $returnMap)
    {
        $observer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();

        $observer->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects($this->atLeastOnce())
            ->method('getDataByKey')
            ->willReturnMap(
                $returnMap
            );

        return $observer;
    }
}

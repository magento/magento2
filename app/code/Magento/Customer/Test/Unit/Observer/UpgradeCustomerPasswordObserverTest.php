<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Observer\UpgradeCustomerPasswordObserver;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** for testing upgrade password observer
 */
class UpgradeCustomerPasswordObserverTest extends TestCase
{
    /**
     * @var UpgradeCustomerPasswordObserver
     */
    protected $model;

    /**
     * @var Encryptor|MockObject
     */
    protected $encryptorMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepository;

    /**
     * @var CustomerRegistry|MockObject
     */
    protected $customerRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerRepository = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->customerRegistry = $this->getMockBuilder(CustomerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->encryptorMock = $this->getMockBuilder(Encryptor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new UpgradeCustomerPasswordObserver(
            $this->encryptorMock,
            $this->customerRegistry,
            $this->customerRepository
        );
    }

    /**
     * Unit test for verifying customers password upgrade observer
     */
    public function testUpgradeCustomerPassword()
    {
        $customerId = '1';
        $password = 'password';
        $passwordHash = 'hash:salt:999';
        $model = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $customer = $this->getMockBuilder(CustomerInterface::class)
            ->addMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerSecure = $this->getMockBuilder(CustomerSecure::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPasswordHash', 'setPasswordHash'])
            ->getMock();
        $model->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($customerId);
        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customer);
        $this->customerRegistry->expects($this->once())
            ->method('retrieveSecureData')
            ->with($customerId)
            ->willReturn($customerSecure);
        $customerSecure->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn($passwordHash);
        $this->encryptorMock->expects($this->once())
            ->method('validateHashVersion')
            ->with($passwordHash)
            ->willReturn(false);
        $this->encryptorMock->expects($this->once())
            ->method('getHash')
            ->with($password, true)
            ->willReturn($passwordHash);
        $customerSecure->expects($this->once())
            ->method('setPasswordHash')
            ->with($passwordHash);
        $this->customerRepository->expects($this->once())
            ->method('save')
            ->with($customer);
        $event = new DataObject();
        $event->setData(['password' => 'password', 'model' => $model]);
        $observerMock = new Observer();
        $observerMock->setEvent($event);
        $this->model->execute($observerMock);
    }
}

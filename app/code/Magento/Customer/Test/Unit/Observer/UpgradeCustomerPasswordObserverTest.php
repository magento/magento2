<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Observer\UpgradeCustomerPasswordObserver;

/**
 * Class UpgradeCustomerPasswordObserverTest for testing upgrade password observer
 */
class UpgradeCustomerPasswordObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UpgradeCustomerPasswordObserver
     */
    protected $model;

    /**
     * @var \Magento\Framework\Encryption\Encryptor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $encryptorMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerRegistry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerRepository = $this
            ->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->getMockForAbstractClass();
        $this->customerRegistry = $this->getMockBuilder(\Magento\Customer\Model\CustomerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\Encryptor::class)
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
        $model = $this->getMockBuilder(\Magento\Customer\Model\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $customer = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerSecure = $this->getMockBuilder(\Magento\Customer\Model\Data\CustomerSecure::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPasswordHash', 'setPasswordHash'])
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
        $event = new \Magento\Framework\DataObject();
        $event->setData(['password' => 'password', 'model' => $model]);
        $observerMock = new \Magento\Framework\Event\Observer();
        $observerMock->setEvent($event);
        $this->model->execute($observerMock);
    }
}

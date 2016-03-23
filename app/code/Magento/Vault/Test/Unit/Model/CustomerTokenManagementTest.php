<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Unit\Model;

use Magento\Customer\Model\Session;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Vault\Model\VaultPaymentInterface;

class CustomerTokenManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentTokenManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTokenManagementMock;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var VaultPaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $vaultPayment;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var CustomerTokenManagement
     */
    private $tokenManagement;

    protected function setUp()
    {
        $this->paymentTokenManagementMock = $this->getMockBuilder(PaymentTokenManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->vaultPayment = $this->getMock(VaultPaymentInterface::class);
        $this->storeManager = $this->getMock(StoreManagerInterface::class);
        $this->store = $this->getMock(StoreInterface::class);

        $this->tokenManagement = new CustomerTokenManagement(
            $this->vaultPayment,
            $this->paymentTokenManagementMock,
            $this->customerSessionMock,
            $this->storeManager
        );
    }

    public function testGetCustomerSessionTokensNonRegisteredCustomer()
    {
        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->paymentTokenManagementMock->expects(static::never())
            ->method('getVisibleAvailableTokens');

        $this->tokenManagement->getCustomerSessionTokens();
    }

    public function testGetCustomerSessionTokensNoActiveVaultProvider()
    {
        $customerId = 1;
        $storeId = 1;
        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->with(null)
            ->willReturn($this->store);
        $this->store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
            ->willReturn(false);

        $this->paymentTokenManagementMock->expects(static::never())
            ->method('getVisibleAvailableTokens');

        $this->tokenManagement->getCustomerSessionTokens();
    }

    public function testGetCustomerSessionTokens()
    {
        $customerId = 1;
        $providerCode = 'vault_provider';
        $storeId = 1;
        $token = $this->getMock(PaymentTokenInterface::class);
        $expectation = [$token];

        $this->customerSessionMock->expects(self::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->storeManager->expects(static::once())
            ->method('getStore')
            ->with(null)
            ->willReturn($this->store);
        $this->store->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);
        $this->vaultPayment->expects(static::once())
            ->method('isActive')
            ->with($storeId)
            ->willReturn(true);
        $this->vaultPayment->expects(static::once())
            ->method('getProviderCode')
            ->with($storeId)
            ->willReturn($providerCode);

        $this->paymentTokenManagementMock->expects(static::once())
            ->method('getVisibleAvailableTokens')
            ->with($customerId, $providerCode)
            ->willReturn($expectation);

        static::assertEquals(
            $expectation,
            $this->tokenManagement->getCustomerSessionTokens()
        );
    }
}

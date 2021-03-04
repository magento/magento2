<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Checkout;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Model\GuestShippingInformationManagement;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session as PersistenceSession;
use Magento\Persistent\Model\Checkout\GuestShippingInformationManagementPlugin;
use Magento\Persistent\Model\QuoteManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GuestShippingInformationManagementPluginTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private $persistenceDataHelper;

    /**
     * @var PersistenceSession|MockObject
     */
    private $persistenceSessionHelper;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSession;

    /**
     * @var QuoteManager|MockObject
     */
    private $quoteManager;
    /**
     * @var GuestShippingInformationManagementPlugin
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->persistenceDataHelper = $this->createMock(Data::class);
        $this->persistenceSessionHelper = $this->createMock(PersistenceSession::class);
        $this->customerSession = $this->createMock(CustomerSession::class);
        $this->quoteManager = $this->createMock(QuoteManager::class);
        $this->model = new GuestShippingInformationManagementPlugin(
            $this->persistenceDataHelper,
            $this->persistenceSessionHelper,
            $this->customerSession,
            $this->quoteManager
        );
    }

    /**
     * @param array $paymentMethods
     * @param bool $isLoggedIn
     * @param bool $isPersistentSessionEnabled
     * @param bool $isPersistentCartEnabled
     * @param bool $isCartPersistent
     * @param bool $isCartConverted
     * @dataProvider afterSaveAddressInformationDataProvider
     */
    public function testAfterSaveAddressInformation(
        array $paymentMethods,
        bool $isLoggedIn,
        bool $isPersistentSessionEnabled,
        bool $isPersistentCartEnabled,
        bool $isCartPersistent,
        bool $isCartConverted
    ): void {
        $subject = $this->createMock(GuestShippingInformationManagement::class);
        $result = $this->getMockForAbstractClass(PaymentDetailsInterface::class);
        $result->method('getPaymentMethods')
            ->willReturn($paymentMethods);
        $this->customerSession->method('isLoggedIn')
            ->willReturn($isLoggedIn);
        $this->persistenceSessionHelper->method('isPersistent')
            ->willReturn($isPersistentSessionEnabled);
        $this->persistenceDataHelper->method('isShoppingCartPersist')
            ->willReturn($isPersistentCartEnabled);
        $this->quoteManager->method('isPersistent')
            ->willReturn($isCartPersistent);
        $this->customerSession->expects($this->exactly($isCartConverted ? 1 : 0))
            ->method('setCustomerId')
            ->with(null);
        $this->customerSession->expects($this->exactly($isCartConverted ? 1 : 0))
            ->method('setCustomerGroupId')
            ->with(null);
        $this->quoteManager->expects($this->exactly($isCartConverted ? 1 : 0))
            ->method('convertCustomerCartToGuest');
        $this->assertSame($result, $this->model->afterSaveAddressInformation($subject, $result));
    }

    /**
     * @return array
     */
    public function afterSaveAddressInformationDataProvider(): array
    {
        return [
            [['paypal'], false, true, true, true, true],
            [['paypal'], true, true, true, true, false],
            [['paypal', 'money_order'], false, true, true, true, false],
            [['paypal', 'money_order'], true, true, true, true, false],
        ];
    }
}

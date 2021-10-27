<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Backpressure\IdentityProviderInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\LimitConfigManagerInterface;
use Magento\Framework\Webapi\Backpressure\BackpressureContextFactory;
use Magento\Quote\Model\Backpressure\OrderLimitConfigManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackpressureTest extends TestCase
{
    /**
     * @var BackpressureContextFactory
     */
    private $webapiContextFactory;

    /**
     * @var LimitConfigManagerInterface
     */
    private $limitConfigManager;

    /**
     * @var IdentityProviderInterface|MockObject
     */
    private $identityProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->identityProvider = $this->createMock(IdentityProviderInterface::class);
        $this->webapiContextFactory = Bootstrap::getObjectManager()->create(
            BackpressureContextFactory::class,
            ['identityProvider' => $this->identityProvider]
        );
        $this->limitConfigManager = Bootstrap::getObjectManager()->get(LimitConfigManagerInterface::class);
    }

    /**
     * Verify that backpressure is configured for guests.
     *
     * @return void
     * @magentoConfigFixture current_store sales/backpressure/enabled 1
     * @magentoConfigFixture current_store sales/backpressure/limit 100
     * @magentoConfigFixture current_store sales/backpressure/guest_limit 50
     * @magentoConfigFixture current_store sales/backpressure/period 60
     */
    public function testConfiguredForGuest(): void {
        $this->identityProvider->method('fetchIdentityType')->willReturn(ContextInterface::IDENTITY_TYPE_IP);
        $this->identityProvider->method('fetchIdentity')->willReturn('127.0.0.1');

        $context = $this->webapiContextFactory->create(
            GuestPaymentInformationManagementInterface::class,
            'savePaymentInformationAndPlaceOrder',
            '/V1/guest-carts/:cartId/payment-information'
        );
        $this->assertEquals(OrderLimitConfigManager::REQUEST_TYPE_ID, $context->getTypeId());

        $limits = $this->limitConfigManager->readLimit($context);
        $this->assertEquals(50, $limits->getLimit());
        $this->assertEquals(60, $limits->getPeriod());
    }

    /**
     * Verify that backpressure is configured for customers.
     *
     * @return void
     * @magentoConfigFixture current_store sales/backpressure/enabled 1
     * @magentoConfigFixture current_store sales/backpressure/limit 100
     * @magentoConfigFixture current_store sales/backpressure/guest_limit 50
     * @magentoConfigFixture current_store sales/backpressure/period 60
     */
    public function testConfiguredForCustomer(): void {
        $this->identityProvider->method('fetchIdentityType')->willReturn(ContextInterface::IDENTITY_TYPE_CUSTOMER);
        $this->identityProvider->method('fetchIdentity')->willReturn('42');

        $context = $this->webapiContextFactory->create(
            PaymentInformationManagementInterface::class,
            'savePaymentInformationAndPlaceOrder',
            '/V1/carts/mine/payment-information'
        );
        $this->assertEquals(OrderLimitConfigManager::REQUEST_TYPE_ID, $context->getTypeId());

        $limits = $this->limitConfigManager->readLimit($context);
        $this->assertEquals(100, $limits->getLimit());
        $this->assertEquals(60, $limits->getPeriod());
    }
}

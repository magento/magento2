<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Plugin;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentMethodListInterface;
use Magento\Vault\Plugin\PaymentVaultInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for payment vault information management plugin
 */
class PaymentVaultInformationManagementTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var PaymentMethodListInterface|MockObject
     */
    private $paymentMethodList;

    /**
     * @var PaymentVaultInformationManagement
     */
    private $plugin;

    /**
     * @var PaymentInformationManagementInterface|MockObject
     */
    private $paymentInformationManagement;

    /**
     * @var PaymentInterface|MockObject
     */
    private $payment;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMockForAbstractClass();
        $this->paymentMethodList = $this->getMockBuilder(PaymentMethodListInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getActiveList'])
            ->getMockForAbstractClass();
        $this->paymentInformationManagement = $this
            ->getMockBuilder(PaymentInformationManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->payment = $this->getMockBuilder(PaymentInterface::class)
            ->onlyMethods(['setMethod'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->plugin = new PaymentVaultInformationManagement($this->paymentMethodList, $this->storeManager);
    }

    /**
     * Test payment method for vault before saving payment information
     *
     * @param string $requestPaymentMethodCode
     * @param string $methodCode
     * @dataProvider vaultPaymentMethodDataProvider
     *
     * @return void
     */
    public function testBeforeSavePaymentInformation($requestPaymentMethodCode, $methodCode): void
    {
        $this->store->method('getId')
            ->willReturn(1);
        $this->storeManager->method('getStore')
            ->willReturn($this->store);
        $activeVaultMethod = $this->getMockBuilder(PaymentInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCode', 'getProviderCode'])
            ->getMockForAbstractClass();
        $activeVaultMethod->method('getCode')
            ->willReturn($methodCode);
        $this->paymentMethodList->method('getActiveList')
            ->willReturn([$activeVaultMethod]);
        $this->payment->method('getMethod')
            ->willReturn($requestPaymentMethodCode);
        $this->payment->expects($this->once())
            ->method('setMethod')
            ->with($methodCode);

        $this->plugin->beforeSavePaymentInformation(
            $this->paymentInformationManagement,
            '1',
            $this->payment,
            null
        );
    }

    /**
     * Data provider for BeforeSavePaymentInformation.
     *
     * @return array
     */
    public function vaultPaymentMethodDataProvider(): array
    {
        return [
            ['braintree_cc_vault_01', 'braintree_cc_vault'],
        ];
    }
}

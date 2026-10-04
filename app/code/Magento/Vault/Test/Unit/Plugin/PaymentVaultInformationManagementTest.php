<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Vault\Test\Unit\Plugin;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\PaymentMethodListInterface;
use Magento\Vault\Model\VaultPaymentInterface;
use Magento\Vault\Plugin\PaymentVaultInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for payment vault information management plugin
 */
class PaymentVaultInformationManagementTest extends TestCase
{
    use MockCreationTrait;

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
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->store = $this->createMock(StoreInterface::class);
        $this->paymentMethodList = $this->createMock(PaymentMethodListInterface::class);
        $this->paymentInformationManagement = $this->createMock(PaymentInformationManagementInterface::class);
        $this->payment = $this->createMock(PaymentInterface::class);
        $this->plugin = new PaymentVaultInformationManagement($this->paymentMethodList, $this->storeManager);
    }

    /**
     * Test payment method for vault before saving payment information
     *
     * @param string $requestPaymentMethodCode
     * @param string $methodCode
     *
     * @return void
     */
    #[DataProvider('vaultPaymentMethodDataProvider')]
    public function testBeforeSavePaymentInformation($requestPaymentMethodCode, $methodCode): void
    {
        $this->store->method('getId')
            ->willReturn(1);
        $this->storeManager->method('getStore')
            ->willReturn($this->store);
        $activeVaultMethod = $this->createMock(VaultPaymentInterface::class);
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
    public static function vaultPaymentMethodDataProvider(): array
    {
        return [
            ['braintree_cc_vault_01', 'braintree_cc_vault'],
        ];
    }
}

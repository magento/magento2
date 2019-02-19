<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider as PayPalConfigProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Vault\Api\PaymentMethodListInterface;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Contains tests for vault payment list methods
 */
class PaymentMethodListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var int
     */
    private $storeId;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->storeId = $objectManager->get(StoreManagerInterface::class)
            ->getStore()
            ->getId();
        $this->paymentMethodList = $objectManager->get(PaymentMethodListInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Braintree/_files/payments.php
     */
    public function testGetList()
    {
        $vaultPayments = $this->paymentMethodList->getList($this->storeId);

        static::assertNotEmpty($vaultPayments);

        $paymentCodes = array_map(function (VaultPaymentInterface $payment) {
            return $payment->getCode();
        }, $vaultPayments);

        $expectedCodes = [
            PayPalConfigProvider::PAYPAL_VAULT_CODE,
            ConfigProvider::CC_VAULT_CODE
        ];
        static::assertNotEmpty(array_intersect($expectedCodes, $paymentCodes));
    }

    /**
     * @magentoDataFixture Magento/Braintree/_files/payments.php
     */
    public function testGetActiveList()
    {
        $vaultPayments = $this->paymentMethodList->getActiveList($this->storeId);

        static::assertNotEmpty($vaultPayments);
        $paymentCodes = array_map(function ($payment) {
            return $payment->getCode();
        }, $vaultPayments);
        static::assertContains(PayPalConfigProvider::PAYPAL_VAULT_CODE, $paymentCodes);
    }
}

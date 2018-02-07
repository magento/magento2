<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Constraint\AssertFieldsAreActive;
use Magento\Payment\Test\Constraint\AssertFieldsAreDisabled;
use Magento\Payment\Test\Constraint\AssertFieldsAreEnabled;
use Magento\Payment\Test\Constraint\AssertFieldsArePresent;

/**
 * Check PayPal Express Checkout configuration.
 */
class CheckExpressConfigStep implements TestStepInterface
{
    /**
     * Payments configuration page.
     *
     * @var SystemConfigEditSectionPayment
     */
    private $systemConfigEditSectionPayment;

    /**
     * @var AssertFieldsAreDisabled
     */
    private $assertFieldsAreDisabled;

    /**
     * @var AssertFieldsArePresent
     */
    private $assertFieldsArePresent;

    /**
     * @var AssertFieldsAreActive
     */
    private $assertFieldsAreActive;

    /**
     * @var AssertFieldsAreEnabled
     */
    private $assertFieldsAreEnabled;

    /**
     * Country code.
     *
     * @var string
     */
    private $countryCode;

    /**
     * Payment sections on Payments configuration page.
     *
     * @var array
     */
    private $sections;

    /**
     * @var \Magento\Paypal\Test\Block\System\Config\ExpressCheckout
     */
    private $expressCheckoutConfigBlock;

    /**
     * @param SystemConfigEditSectionPayment $systemConfigEditSectionPayment
     * @param AssertFieldsAreDisabled $assertFieldsAreDisabled
     * @param AssertFieldsArePresent $assertFieldsArePresent
     * @param AssertFieldsAreActive $assertFieldsAreActive
     * @param AssertFieldsAreEnabled $assertFieldsAreEnabled
     * @param string $countryCode
     * @param array $sections
     */
    public function __construct(
        SystemConfigEditSectionPayment $systemConfigEditSectionPayment,
        AssertFieldsAreDisabled $assertFieldsAreDisabled,
        AssertFieldsArePresent $assertFieldsArePresent,
        AssertFieldsAreActive $assertFieldsAreActive,
        AssertFieldsAreEnabled $assertFieldsAreEnabled,
        $countryCode,
        array $sections
    ) {
        $this->systemConfigEditSectionPayment = $systemConfigEditSectionPayment;
        $this->assertFieldsAreDisabled = $assertFieldsAreDisabled;
        $this->assertFieldsArePresent = $assertFieldsArePresent;
        $this->assertFieldsAreActive = $assertFieldsAreActive;
        $this->assertFieldsAreEnabled = $assertFieldsAreEnabled;
        $this->countryCode = $countryCode;
        $this->sections = $sections;
        $this->expressCheckoutConfigBlock = $this->systemConfigEditSectionPayment->getExpressCheckoutConfigBlock();
    }

    /**
     * Run step for checking PayPal Express Checkout configuration.
     *
     * @return void
     */
    public function run()
    {
        $this->systemConfigEditSectionPayment->open();
        $this->systemConfigEditSectionPayment->getPaymentsConfigBlock()->switchMerchantCountry($this->countryCode);
        $this->systemConfigEditSectionPayment->getPaymentsConfigBlock()->expandPaymentSections($this->sections);
        $this->enableExpressCheckout();
        $this->disableExpressCheckout();
    }

    /**
     * Enables Express Checkout and makes assertions for fields.
     *
     * @return void
     */
    private function enableExpressCheckout()
    {
        $this->expressCheckoutConfigBlock->clickConfigureButton();
        $this->expressCheckoutConfigBlock->clearCredentials();
        $enablers = $this->expressCheckoutConfigBlock->getEnablerFields();
        $this->assertFieldsAreDisabled->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->expressCheckoutConfigBlock->specifyCredentials();
        $this->expressCheckoutConfigBlock->enableExpressCheckout();
        $expressFields = $this->expressCheckoutConfigBlock->getFields();
        $this->assertFieldsArePresent->processAssert(
            $this->systemConfigEditSectionPayment,
            [$expressFields['Merchant Account ID'], $expressFields['Sort Order PayPal Credit']]
        );
        $this->assertFieldsAreActive->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable In-Context Checkout Experience'], $enablers['Enable PayPal Credit']]
        );
        $this->assertFieldsAreEnabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable In-Context Checkout Experience'], $enablers['Enable PayPal Credit']]
        );
        $this->expressCheckoutConfigBlock->specifyMerchantAccountId();
        $this->systemConfigEditSectionPayment->getPageActions()->save();
        $this->systemConfigEditSectionPayment->getMessagesBlock()->waitSuccessMessageAndRefreshPage();
    }

    /**
     * Disables Express Checkout and makes assertions for fields.
     *
     * @return void
     */
    private function disableExpressCheckout()
    {
        $enablers = $this->expressCheckoutConfigBlock->getEnablerFields();
        $this->expressCheckoutConfigBlock->clickConfigureButton();
        $this->assertFieldsAreActive->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->assertFieldsAreEnabled->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->expressCheckoutConfigBlock->disableExpressCheckout();
        $this->assertFieldsAreDisabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable In-Context Checkout Experience'], $enablers['Enable PayPal Credit']]
        );
        $this->systemConfigEditSectionPayment->getPageActions()->save();
        $this->systemConfigEditSectionPayment->getMessagesBlock()->waitSuccessMessageAndRefreshPage();
    }
}

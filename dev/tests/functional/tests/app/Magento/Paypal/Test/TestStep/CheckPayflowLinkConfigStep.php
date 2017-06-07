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
 * Check PayPal Payflow Link configuration.
 */
class CheckPayflowLinkConfigStep implements TestStepInterface
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
     * @var \Magento\Paypal\Test\Block\System\Config\PayflowLink
     */
    private $payflowLinkConfigBlock;

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
        $this->payflowLinkConfigBlock = $this->systemConfigEditSectionPayment->getPayflowLinkConfigBlock();
    }

    /**
     * Run step for checking Payflow Link configuration.
     *
     * @return void
     */
    public function run()
    {
        $this->systemConfigEditSectionPayment->getPaymentsConfigBlock()->expandPaymentSections($this->sections);
        $this->enablePayflowLink();
        $this->disablePayflowLink();
    }

    /**
     * Enables Payflow Link and makes assertions for fields.
     *
     * @return void
     */
    private function enablePayflowLink()
    {
        $this->payflowLinkConfigBlock->clickConfigureButton();
        $this->payflowLinkConfigBlock->clearCredentials();
        $enablers = $this->payflowLinkConfigBlock->getEnablerFields();
        $this->assertFieldsAreDisabled->processAssert(
            $this->systemConfigEditSectionPayment,
            $enablers
        );
        $this->payflowLinkConfigBlock->specifyCredentials();
        $this->assertFieldsAreActive->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable Payflow Link']]
        );
        $this->assertFieldsAreDisabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable Express Checkout'], $enablers['Enable PayPal Credit']]
        );
        $this->payflowLinkConfigBlock->enablePayflowLink();
        $this->assertFieldsAreActive->processAssert(
            $this->systemConfigEditSectionPayment,
            $enablers
        );
        $this->assertFieldsAreEnabled->processAssert(
            $this->systemConfigEditSectionPayment,
            $enablers
        );
        $this->systemConfigEditSectionPayment->getPageActions()->save();
        $this->systemConfigEditSectionPayment->getMessagesBlock()->waitSuccessMessage();
    }

    /**
     * Disables Payflow Link and makes assertions for fields.
     *
     * @return void
     */
    private function disablePayflowLink()
    {
        $enablers = $this->payflowLinkConfigBlock->getEnablerFields();
        $this->payflowLinkConfigBlock->clickConfigureButton();
        $this->assertFieldsAreActive->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->assertFieldsAreEnabled->processAssert(
            $this->systemConfigEditSectionPayment,
            $enablers
        );
        $this->payflowLinkConfigBlock->disablePayflowLink();
        $this->assertFieldsAreDisabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable Express Checkout'], $enablers['Enable PayPal Credit']]
        );
        $this->systemConfigEditSectionPayment->getPageActions()->save();
        $this->systemConfigEditSectionPayment->getMessagesBlock()->waitSuccessMessage();
    }
}

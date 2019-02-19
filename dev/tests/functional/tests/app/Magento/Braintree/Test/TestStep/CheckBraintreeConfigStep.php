<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\TestStep;

use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionPayment;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Constraint\AssertFieldsAreActive;
use Magento\Payment\Test\Constraint\AssertFieldsAreDisabled;
use Magento\Payment\Test\Constraint\AssertFieldsAreEnabled;
use Magento\Payment\Test\Constraint\AssertFieldsArePresent;

/**
 * Check configuration of Braintree payment method.
 */
class CheckBraintreeConfigStep implements TestStepInterface
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
     * @var \Magento\Braintree\Test\Block\System\Config\Braintree
     */
    private $braintreeConfigBlock;

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
        $this->braintreeConfigBlock = $this->systemConfigEditSectionPayment->getBraintreeConfigBlock();
    }

    /**
     * Run step for checking Braintree configuration.
     *
     * @return void
     */
    public function run()
    {
        $this->systemConfigEditSectionPayment->getPaymentsConfigBlock()->expandPaymentSections($this->sections);
        $this->enableBraintree();
        $this->disableBraintree();
    }

    /**
     * Enables Braintree and makes assertions for fields.
     *
     * @return void
     */
    private function enableBraintree()
    {
        $this->braintreeConfigBlock->clickConfigureButton();
        $this->braintreeConfigBlock->clearCredentials();
        $enablers = $this->braintreeConfigBlock->getEnablerFields();
        $this->assertFieldsAreDisabled->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->braintreeConfigBlock->specifyCredentials();
        $this->assertFieldsAreActive->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->braintreeConfigBlock->enableBraintree();
        $this->assertFieldsAreActive->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->systemConfigEditSectionPayment->getPageActions()->save();
        $this->systemConfigEditSectionPayment->getMessagesBlock()->waitSuccessMessage();
    }

    /**
     * Disables Express Checkout and makes assertions for fields.
     *
     * @return void
     */
    private function disableBraintree()
    {
        $enablers = $this->braintreeConfigBlock->getEnablerFields();
        $this->braintreeConfigBlock->clickConfigureButton();
        $this->assertFieldsAreActive->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->assertFieldsAreEnabled->processAssert(
            $this->systemConfigEditSectionPayment,
            [$enablers['Enable this Solution']]
        );
        $this->braintreeConfigBlock->disableBraintree();
        $this->assertFieldsAreActive->processAssert($this->systemConfigEditSectionPayment, $enablers);
        $this->systemConfigEditSectionPayment->getPageActions()->save();
        $this->systemConfigEditSectionPayment->getMessagesBlock()->waitSuccessMessage();
    }
}

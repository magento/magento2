<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Customer\Test\TestStep\DeleteCustomerStep;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Signifyd\Test\Constraint\AssertCaseInfoOnSignifydConsole;
use Magento\Signifyd\Test\Fixture\SignifydAddress;
use Magento\Signifyd\Test\Fixture\SignifydData;
use Magento\Signifyd\Test\Page\SignifydConsole\SignifydCases;
use Magento\Signifyd\Test\Page\SignifydConsole\SignifydNotifications;

/**
 * Observe case information in Signifyd console step.
 */
class SignifydObserveCaseStep implements TestStepInterface
{
    /**
     * Case information on Signifyd console assertion.
     *
     * @var AssertCaseInfoOnSignifydConsole
     */
    private $assertCaseInfo;

    /**
     * Billing address fixture.
     *
     * @var SignifydAddress
     */
    private $signifydAddress;

    /**
     * Signifyd cases page.
     *
     * @var SignifydCases
     */
    private $signifydCases;

    /**
     * Signifyd notifications page.
     *
     * @var SignifydNotifications
     */
    private $signifydNotifications;

    /**
     * Signifyd data fixture.
     *
     * @var array
     */
    private $signifydData;

    /**
     * Signifyd cancel order step.
     *
     * @var SignifydCancelOrderStep
     */
    private $signifydCancelOrderStep;

    /**
     * Delete customer step.
     *
     * @var DeleteCustomerStep
     */
    private $deleteCustomerStep;

    /**
     * Prices list.
     *
     * @var array
     */
    private $prices;

    /**
     * Order id.
     *
     * @var string
     */
    private $orderId;

    /**
     * @param AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole
     * @param SignifydAddress $signifydAddress
     * @param SignifydCases $signifydCases
     * @param SignifydNotifications $signifydNotifications
     * @param SignifydData $signifydData
     * @param SignifydCancelOrderStep $signifydCancelOrderStep
     * @param DeleteCustomerStep $deleteCustomerStep
     * @param array $prices
     * @param $orderId
     */
    public function __construct(
        AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole,
        SignifydAddress $signifydAddress,
        SignifydCases $signifydCases,
        SignifydNotifications $signifydNotifications,
        SignifydData $signifydData,
        SignifydCancelOrderStep $signifydCancelOrderStep,
        DeleteCustomerStep $deleteCustomerStep,
        array $prices,
        $orderId
    ) {
        $this->assertCaseInfo = $assertCaseInfoOnSignifydConsole;
        $this->signifydAddress = $signifydAddress;
        $this->signifydCases = $signifydCases;
        $this->signifydNotifications = $signifydNotifications;
        $this->signifydData = $signifydData;
        $this->signifydCancelOrderStep = $signifydCancelOrderStep;
        $this->deleteCustomerStep = $deleteCustomerStep;
        $this->prices = $prices;
        $this->orderId = $orderId;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->signifydCases->open();
        $this->signifydCases->getCaseSearchBlock()
            ->searchCaseByCustomerName($this->signifydAddress->getFirstname());
        $this->signifydCases->getCaseSearchBlock()->selectCase();
        $this->signifydCases->getCaseInfoBlock()->flagCase($this->signifydData->getCaseFlag());

        $this->assertCaseInfo->processAssert(
            $this->signifydCases,
            $this->signifydAddress,
            $this->signifydData,
            $this->prices,
            $this->orderId,
            $this->getCustomerFullName($this->signifydAddress)
        );
    }

    /**
     * Cancel order if test fails, or in the end of variation.
     * Cleanup customer for next variations.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->signifydCancelOrderStep->run();
        $this->deleteCustomerStep->run();
    }

    /**
     * Gets customer full name.
     *
     * @param SignifydAddress $billingAddress
     * @return string
     */
    private function getCustomerFullName(SignifydAddress $billingAddress)
    {
        return sprintf('%s %s', $billingAddress->getFirstname(), $billingAddress->getLastname());
    }
}

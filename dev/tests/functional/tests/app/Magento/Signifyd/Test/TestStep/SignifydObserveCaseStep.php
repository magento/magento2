<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
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
     * Prices list.
     *
     * @var array
     */
    private $prices;

    /**
     * Order fixture.
     *
     * @var string
     */
    private $order;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $testStepFactory;

    /**
     * @param AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole
     * @param SignifydAddress $signifydAddress
     * @param SignifydCases $signifydCases
     * @param SignifydNotifications $signifydNotifications
     * @param SignifydData $signifydData
     * @param OrderInjectable $order
     * @param TestStepFactory $testStepFactory
     * @param array $prices
     */
    public function __construct(
        AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole,
        SignifydAddress $signifydAddress,
        SignifydCases $signifydCases,
        SignifydNotifications $signifydNotifications,
        SignifydData $signifydData,
        OrderInjectable $order,
        TestStepFactory $testStepFactory,
        array $prices
    ) {
        $this->assertCaseInfo = $assertCaseInfoOnSignifydConsole;
        $this->signifydAddress = $signifydAddress;
        $this->signifydCases = $signifydCases;
        $this->signifydNotifications = $signifydNotifications;
        $this->signifydData = $signifydData;
        $this->order = $order;
        $this->testStepFactory = $testStepFactory;
        $this->prices = $prices;
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
            $this->order->getId(),
            $this->getCustomerFullName($this->signifydAddress)
        );
    }

    /**
     * Cancel order if test fails, or in the end of variation.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->testStepFactory->create(
            SignifydCancelOrderStep::class,
            ['order' => $this->order]
        )->run();
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

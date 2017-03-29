<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Signifyd\Test\Constraint\AssertCaseInfoOnSignifydConsole;
use Magento\Signifyd\Test\Fixture\SignifydAddress;
use Magento\Signifyd\Test\Page\SignifydConsole\SignifydCases;

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
     * Signifyd cases page.
     *
     * @var SignifydCases
     */
    private $signifydCases;

    /**
     * Billing address fixture.
     *
     * @var SignifydAddress
     */
    private $signifydAddress;

    /**
     * Prices list.
     *
     * @var array
     */
    private $prices;

    /**
     * Array of Signifyd config data.
     *
     * @var array
     */
    private $signifydData;

    /**
     * Order id.
     *
     * @var string
     */
    private $orderId;

    /**
     * @param AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole
     * @param SignifydCases $signifydCases
     * @param SignifydAddress $signifydAddress
     * @param array $prices
     * @param array $signifydData
     * @param string $orderId
     */
    public function __construct(
        AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole,
        SignifydCases $signifydCases,
        SignifydAddress $signifydAddress,
        array $prices,
        array $signifydData,
        $orderId
    ) {
        $this->assertCaseInfo = $assertCaseInfoOnSignifydConsole;
        $this->signifydCases = $signifydCases;
        $this->signifydAddress = $signifydAddress;
        $this->prices = $prices;
        $this->signifydData = $signifydData;
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
        $this->signifydCases->getCaseInfoBlock()->flagCase($this->signifydData['caseFlag']);

        $this->assertCaseInfo->processAssert(
            $this->signifydCases,
            $this->signifydAddress,
            $this->prices,
            $this->orderId,
            $this->getCustomerFullName($this->signifydAddress),
            $this->signifydData
        );
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

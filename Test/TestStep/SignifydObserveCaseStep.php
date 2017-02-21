<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Signifyd\Test\Constraint\AssertCaseInfoOnSignifydConsole;
use Magento\Signifyd\Test\Page\Sandbox\SignifydCases;

/**
 * Observe case information in Signifyd console step.
 */
class SignifydObserveCaseStep implements TestStepInterface
{
    /**
     * Signifyd cases page.
     *
     * @var SignifydCases
     */
    private $signifydCases;

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    private $customer;

    /**
     * Billing address fixture.
     *
     * @var Address
     */
    private $billingAddress;

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
     * Array of Signifyd config data.
     *
     * @var array
     */
    private $signifydData;

    /**
     * Case information on Signifyd console assertion.
     *
     * @var AssertCaseInfoOnSignifydConsole
     */
    private $assertCaseInfoOnSignifydConsole;

    /**
     * @param SignifydCases $signifydCases
     * @param Customer $customer
     * @param Address $billingAddress
     * @param array $prices
     * @param string $orderId
     * @param array $signifydData
     * @param AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole
     */
    public function __construct(
        SignifydCases $signifydCases,
        Customer $customer,
        Address $billingAddress,
        array $prices,
        $orderId,
        array $signifydData,
        AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole
    ) {
        $this->signifydCases = $signifydCases;
        $this->customer = $customer;
        $this->billingAddress = $billingAddress;
        $this->prices = $prices;
        $this->orderId = $orderId;
        $this->assertCaseInfoOnSignifydConsole = $assertCaseInfoOnSignifydConsole;
        $this->signifydData = $signifydData;
    }

    /**
     * Run step flow
     *
     * @return void
     */
    public function run()
    {
        $this->signifydCases->open();
        $this->signifydCases->getCaseSearchBlock()
            ->searchCaseByCustomerName($this->getCustomerFullName($this->customer));
        $this->signifydCases->getCaseSearchBlock()->selectCase();
        $this->signifydCases->getCaseInfoBlock()->flagCaseAsGood();

        $this->assertCaseInfoOnSignifydConsole->processAssert(
            $this->signifydCases,
            $this->billingAddress,
            $this->prices,
            $this->orderId,
            $this->getCustomerFullName($this->customer),
            $this->signifydData
        );
    }

    /**
     * Gets customer full name.
     *
     * @param Customer $customer
     * @return string
     */
    private function getCustomerFullName(Customer $customer)
    {
        return sprintf('%s %s', $customer->getFirstname(), $customer->getLastname());
    }
}

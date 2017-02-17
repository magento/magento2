<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Signifyd\Test\Constraint\AssertCaseInfo;
use Magento\Signifyd\Test\Fixture\SandboxMerchant;
use Magento\Signifyd\Test\Page\Sandbox\SignifydCases;
use Magento\Signifyd\Test\Page\Sandbox\SignifydLogin;

/**
 * Class SignifydObserveCaseStep
 */
class SignifydObserveCaseStep implements TestStepInterface
{
    /**
     * Signifyd Sandbox merchant fixture.
     *
     * @var SandboxMerchant
     */
    private $sandboxMerchant;

    /**
     * @var SignifydLogin
     */
    private $signifydLogin;

    /**
     * @var SignifydCases
     */
    private $signifydCases;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Address
     */
    private $billingAddress;

    /**
     * @var array
     */
    private $prices;

    /**
     * @var string
     */
    private $orderId;

    /**
     * @var AssertCaseInfo
     */
    private $assertCaseInfo;

    /**
     * ObserveSignifydCaseStep constructor.
     * @param SandboxMerchant $sandboxMerchant
     * @param SignifydLogin $signifydLogin
     * @param SignifydCases $signifydCases
     * @param Customer $customer
     * @param Address $billingAddress
     * @param array $prices
     * @param string $orderId
     * @param AssertCaseInfo $assertCaseInfo
     */
    public function __construct(
        SandboxMerchant $sandboxMerchant,
        SignifydLogin $signifydLogin,
        SignifydCases $signifydCases,
        Customer $customer,
        Address $billingAddress,
        array $prices,
        $orderId,
        AssertCaseInfo $assertCaseInfo
    ) {
        $this->sandboxMerchant = $sandboxMerchant;
        $this->signifydLogin = $signifydLogin;
        $this->signifydCases = $signifydCases;
        $this->customer = $customer;
        $this->billingAddress = $billingAddress;
        $this->prices = $prices;
        $this->orderId = $orderId;
        $this->assertCaseInfo = $assertCaseInfo;
    }

    /**
     * Run step flow
     *
     * @return void
     */
    public function run()
    {
        //Login in Signifyd sandbox provides on signifydSetWebhooksAddress step
        $this->signifydCases->open();
        $this->signifydCases->getCaseSearchBlock()
            ->fillSearchCriteria($this->getCustomerFullName($this->customer));
        $this->signifydCases->getCaseSearchBlock()->searchCase();
        $this->signifydCases->getCaseSearchBlock()->selectCase();
        $this->signifydCases->getCaseInfoBlock()->flagCaseGood();

        $this->assertCaseInfo->processAssert(
            $this->signifydCases,
            $this->billingAddress,
            $this->prices,
            $this->orderId,
            $this->getCustomerFullName($this->customer)
        );
    }

    /**
     * @param Customer $customer
     * @return string
     */
    private function getCustomerFullName(Customer $customer)
    {
        return sprintf('%s %s', $customer->getFirstname(), $customer->getLastname());
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Signifyd\Test\Constraint\AssertCaseInfo;
use Magento\Signifyd\Test\Fixture\SandboxMerchant;
use Magento\Signifyd\Test\Page\Sandbox\SignifydCases;
use Magento\Signifyd\Test\Page\Sandbox\SignifydLogin;

class ObserveSignifydCaseStep implements TestStepInterface
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
     * @var AssertCaseInfo
     */
    private $assertCaseInfo;

    /**
     * @var OrderInjectable
     */
    private $order;

    /**
     * @var array
     */
    private $cartPrice;
    /**
     * @var Address
     */
    private $billingAddress;

    /**
     * @param SandboxMerchant $sandboxMerchant
     * @param SignifydLogin $signifydLogin
     * @param SignifydCases $signifydCases
     * @param Customer $customer
     * @param AssertCaseInfo $assertCaseInfo
     * @param OrderInjectable $order
     */
    public function __construct(
        SandboxMerchant $sandboxMerchant,
        SignifydLogin $signifydLogin,
        SignifydCases $signifydCases,
        Customer $customer,
        AssertCaseInfo $assertCaseInfo,
        OrderInjectable $order,
        Address $billingAddress,
        array $cartPrice
    ) {
        $this->sandboxMerchant = $sandboxMerchant;
        $this->signifydLogin = $signifydLogin;
        $this->signifydCases = $signifydCases;
        $this->customer = $customer;
        $this->assertCaseInfo = $assertCaseInfo;
        $this->order = $order;
        $this->cartPrice = $cartPrice;
        $this->billingAddress = $billingAddress;
    }

    /**
     * Run step flow
     *
     * @return void
     */
    public function run()
    {
        $this->signifydLogin->open();
        $this->signifydLogin->getLoginBlock()->fill($this->sandboxMerchant);
        $this->signifydLogin->getLoginBlock()->sandboxLogin();

        $this->signifydCases->getCaseSearchBlock()
            ->fillSearchCriteria($this->customer->getFirstname() . ' ' . $this->customer->getLastname());
        $this->signifydCases->getCaseSearchBlock()->searchCase();

        $this->signifydCases->getCaseSearchBlock()->selectCase();
        $this->signifydCases->getCaseInfoBlock()->flagCaseGood();

        $this->assertCaseInfo->processAssert($this->signifydCases, $this->customer, $this->order, $this->billingAddress, $this->cartPrice);
    }
}

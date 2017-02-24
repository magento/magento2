<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\TestStep\CancelOrderStep;
use Magento\Signifyd\Test\Constraint\AssertCaseInfoOnSignifydConsole;
use Magento\Signifyd\Test\Fixture\SignifydAddress;
use Magento\Signifyd\Test\Page\Sandbox\SignifydCases;

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
     * Cancel order on backend step.
     *
     * @var CancelOrderStep
     */
    private $cancelOrderStep;

    /**
     * Orders View Page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    private $orderIndex;

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
     * @param CancelOrderStep $cancelOrderStep
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param SignifydCases $signifydCases
     * @param SignifydAddress $signifydAddress
     * @param array $prices
     * @param array $signifydData
     * @param string $orderId
     */
    public function __construct(
        AssertCaseInfoOnSignifydConsole $assertCaseInfoOnSignifydConsole,
        CancelOrderStep $cancelOrderStep,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        SignifydCases $signifydCases,
        SignifydAddress $signifydAddress,
        array $prices,
        array $signifydData,
        $orderId
    ) {
        $this->assertCaseInfo = $assertCaseInfoOnSignifydConsole;
        $this->cancelOrderStep = $cancelOrderStep;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
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
        $this->signifydCases->getCaseInfoBlock()->flagCaseAsGood();

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

    /**
     * Cancel order on backend.
     *
     * Signifyd needs this cleanup for guarantee decline. If we had have many cases
     * with approved guarantees, and same order id, Signifyd will not create
     * guarantee approve status for new cases.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->orderId]);

        if ($this->salesOrderView->getOrderInfoBlock()->getOrderStatus() !== 'Canceled') {
            $this->cancelOrderStep->run();
        }
    }
}

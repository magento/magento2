<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;

/**
 * Case information block.
 */
class CaseInfo extends Block
{
    /**
     * Css selector of "Flag Case As Good" button.
     *
     * @var string
     */
    private $flagCaseAsGoodButton = '[class*="flag-case-good"]';

    /**
     * Css selector of guarantee status.
     *
     * @var string
     */
    private $guaranteeDisposition = '[class*="guarantee-status"]';

    /**
     * Css selector of CVV response description.
     *
     * @var string
     */
    private $cvvResponseDescription = '[ng-bind="caseOrderSummary.cvvResponseDescription"]';

    /**
     * Css selector of CVV response code.
     *
     * @var string
     */
    private $cvvResponseCode = '[ng-bind="caseOrderSummary.cvvResponseCode"]';

    /**
     * Css selector of AVS response description.
     *
     * @var string
     */
    private $avsResponseDescription = '[ng-bind="caseOrderSummary.avsResponseDescription"]';

    /**
     * Css selector of AVS response code.
     *
     * @var string
     */
    private $avsResponseCode = '[ng-bind="caseOrderSummary.avsResponseCode"]';

    /**
     * Css selector of displayed case order id.
     *
     * @var string
     */
    private $orderId = '[ng-bind="currentCase.caseIdDisplay"]';

    /**
     * Css selector of displayed order amount.
     *
     * @var string
     */
    private $orderAmount = '[ng-bind*="currentCase.orderAmount"]';

    /**
     * Css selector of displayed card holder name.
     *
     * @var string
     */
    private $cardHolder = '[data-dropdown="peopleLinks0"]';

    /**
     * Css selector of displayed billing address.
     *
     * @var string
     */
    private $billingAddress = '[data-dropdown="streetLinks0"]';

    /**
     * Flags case as good.
     *
     * @return void
     */
    public function flagCaseAsGood()
    {
        $this->_rootElement->find($this->flagCaseAsGoodButton)->click();
    }

    /**
     * Gets guarantee disposition.
     *
     * @return string
     */
    public function getGuaranteeDisposition()
    {
        return $this->_rootElement->find($this->guaranteeDisposition)->getText();
    }

    /**
     * Gets CVV response.
     *
     * @return string
     */
    public function getCvvResponse()
    {
        return sprintf(
            '%s (%s)',
            $this->_rootElement->find($this->cvvResponseDescription)->getText(),
            $this->_rootElement->find($this->cvvResponseCode)->getText()
        );
    }

    /**
     * Gets AVS response.
     *
     * @return string
     */
    public function getAvsResponse()
    {
        return sprintf(
            '%s (%s)',
            $this->_rootElement->find($this->avsResponseDescription)->getText(),
            $this->_rootElement->find($this->avsResponseCode)->getText()
        );
    }

    /**
     * Gets displayed order id.
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->_rootElement->find($this->orderId)->getText();
    }

    /**
     * Gets displayed order amount.
     *
     * @return string
     */
    public function getOrderAmount()
    {
        return $this->_rootElement->find($this->orderAmount)->getText();
    }

    /**
     * Gets displayed card holder name.
     *
     * @return string
     */
    public function getCardHolder()
    {
        return $this->_rootElement->find($this->cardHolder)->getText();
    }

    /**
     * Gets displayed billing address.
     *
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->_rootElement->find($this->billingAddress)->getText();
    }
}

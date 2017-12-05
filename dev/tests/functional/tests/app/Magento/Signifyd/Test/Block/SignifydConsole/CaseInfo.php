<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\SignifydConsole;

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
     * Css selector of "Flag Case As Bad" button.
     *
     * @var string
     */
    private $flagCaseAsBadButton = '[class*="flag-case-bad"]';

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
     * Locator value for order amount currency.
     *
     * @var string
     */
    private $orderAmountCurrency = '[ng-bind*="currentCase.currency"]';

    /**
     * Css selector of displayed card holder name.
     *
     * @var string
     */
    private $cardHolder = '[data-dropdown="peopleLinks0_card_holders"]';

    /**
     * Css selector of displayed billing address.
     *
     * @var string
     */
    private $billingAddress = '[data-dropdown="streetLinks0"]';

    /**
     * Locator value for "No analysis available" block in "Device" container.
     *
     * @var string
     */
    private $noDeviceAnalysisAvailable = '[ng-hide^="caseAnalysis.deviceAnalysis.details.length"]';

    /**
     * Locator value for "Shipping Price" block.
     *
     * @var string
     */
    private $shippingPrice = '[ng-if$="caseOrderSummary.shipments[0].shippingPrice"]';

    /**
     * Check if device data are present.
     *
     * @return bool
     */
    public function isAvailableDeviceData()
    {
        return !$this->_rootElement->find($this->noDeviceAnalysisAvailable)->isVisible();
    }

    /**
     * Returns shipping price.
     *
     * @return string
     */
    public function getShippingPrice()
    {
        return $this->_rootElement->find($this->shippingPrice)->getText();
    }

    /**
     * Flags case as good or bad.
     *
     * @param string $flagType
     * @return void
     */
    public function flagCase($flagType)
    {
        $flagSelector = ($flagType === 'Good')
            ? $this->flagCaseAsGoodButton
            : $this->flagCaseAsBadButton;

        $this->_rootElement->find($flagSelector)->click();
    }

    /**
     * Flags case as bad.
     *
     * @return void
     */
    public function flagCaseAsBad()
    {
        $this->_rootElement->find($this->flagCaseAsBadButton)->click();
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
     * Returns displayed order amount currency.
     *
     * @return string
     */
    public function getOrderAmountCurrency()
    {
        return $this->_rootElement->find($this->orderAmountCurrency)->getText();
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

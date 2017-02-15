<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sanbox;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

class CaseInfo extends Block
{
    private $flagGoodButton = 'button.flag-case-good';
    private $cvvResponseDescription = '//span[contains(@ng-bind, "caseOrderSummary.cvvResponseDescription")]';
    private $cvvResponseCode = '//span[contains(@ng-bind, "caseOrderSummary.cvvResponseCode")]';
    private $avsResponseDescription = '//span[contains(@ng-bind, "caseOrderSummary.avsResponseDescription")]';
    private $avsResponseCode = '//span[contains(@ng-bind, "caseOrderSummary.avsResponseCode")]';
    private $orderId = '//span[contains(@ng-bind, "currentCase.caseIdDisplay")]';
    private $orderAmount = '//span[contains(@ng-bind, "currentCase.orderAmount")]';
    private $cardHolder = '//a[contains(@data-dropdown, "peopleLinks0")]//span';
    private $billingAddress = '//a[contains(@data-dropdown, "streetLinks0")]';

    public function flagCaseGood()
    {
        $this->_rootElement->find($this->flagGoodButton)->click();
    }

    public function getCvvResponse()
    {
        return sprintf(
            '%s (%s)',
            $this->_rootElement->find($this->cvvResponseDescription, Locator::SELECTOR_XPATH)->getText(),
            $this->_rootElement->find($this->cvvResponseCode, Locator::SELECTOR_XPATH)->getText()
        );
    }

    public function getAvsResponse()
    {
        return sprintf(
            '%s (%s)',
            $this->_rootElement->find($this->avsResponseDescription, Locator::SELECTOR_XPATH)->getText(),
            $this->_rootElement->find($this->avsResponseCode, Locator::SELECTOR_XPATH)->getText()
        );
    }

    public function getOrderId()
    {
        return $this->_rootElement->find($this->orderId, Locator::SELECTOR_XPATH)->getText();
    }

    public function getOrderAmount()
    {
        return $this->_rootElement->find($this->orderAmount, Locator::SELECTOR_XPATH)->getText();
    }

    public function getCardHolder()
    {
        return $this->_rootElement->find($this->cardHolder, Locator::SELECTOR_XPATH)->getText();
    }

    public function getBillingAddress()
    {
        return $this->_rootElement->find($this->billingAddress, Locator::SELECTOR_XPATH)->getText();
    }
}

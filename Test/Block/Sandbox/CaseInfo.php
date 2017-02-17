<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Sandbox;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block with case information.
 */
class CaseInfo extends Block
{
    /**
     * @var string
     */
    private $flagGoodButton = '.flag-case-good';

    /**
     * @var string
     */
    private $guaranteeDisposition = '.guarantee-status';

    /**
     * @var string
     */
    private $cvvResponseDescription = '//span[contains(@ng-bind, "caseOrderSummary.cvvResponseDescription")]';

    /**
     * @var string
     */
    private $cvvResponseCode = '//span[contains(@ng-bind, "caseOrderSummary.cvvResponseCode")]';

    /**
     * @var string
     */
    private $avsResponseDescription = '//span[contains(@ng-bind, "caseOrderSummary.avsResponseDescription")]';

    /**
     * @var string
     */
    private $avsResponseCode = '//span[contains(@ng-bind, "caseOrderSummary.avsResponseCode")]';

    /**
     * @var string
     */
    private $orderId = '//span[contains(@ng-bind, "currentCase.caseIdDisplay")]';

    /**
     * @var string
     */
    private $orderAmount = '//span[contains(@ng-bind, "currentCase.orderAmount")]';

    /**
     * @var string
     */
    private $cardHolder = '//a[contains(@data-dropdown, "peopleLinks0")]//span';

    /**
     * @var string
     */
    private $billingAddress = '//a[contains(@data-dropdown, "streetLinks0")]';

    /**
     * @return void
     */
    public function flagCaseGood()
    {
        $this->_rootElement->find($this->flagGoodButton)->click();
    }

    /**
     * @return array|string
     */
    public function getGuaranteeDisposition()
    {
        return $this->_rootElement->find($this->guaranteeDisposition)->getText();
    }

    /**
     * @return string
     */
    public function getCvvResponse()
    {
        return sprintf(
            '%s (%s)',
            $this->_rootElement->find($this->cvvResponseDescription, Locator::SELECTOR_XPATH)->getText(),
            $this->_rootElement->find($this->cvvResponseCode, Locator::SELECTOR_XPATH)->getText()
        );
    }

    /**
     * @return string
     */
    public function getAvsResponse()
    {
        return sprintf(
            '%s (%s)',
            $this->_rootElement->find($this->avsResponseDescription, Locator::SELECTOR_XPATH)->getText(),
            $this->_rootElement->find($this->avsResponseCode, Locator::SELECTOR_XPATH)->getText()
        );
    }

    /**
     * @return array|string
     */
    public function getOrderId()
    {
        return $this->_rootElement->find($this->orderId, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * @return array|string
     */
    public function getOrderAmount()
    {
        return $this->_rootElement->find($this->orderAmount, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * @return array|string
     */
    public function getCardHolder()
    {
        return $this->_rootElement->find($this->cardHolder, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * @return array|string
     */
    public function getBillingAddress()
    {
        return $this->_rootElement->find($this->billingAddress, Locator::SELECTOR_XPATH)->getText();
    }
}

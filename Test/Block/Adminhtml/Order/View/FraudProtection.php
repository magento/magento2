<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Block\Adminhtml\Order\View;

use Magento\Mtf\Block\Block;

/**
 * Information about fraud protection on order page.
 */
class FraudProtection extends Block
{
    /**
     * Case Status.
     *
     * @var string
     */
    private $caseStatus = 'td.col-case-status';

    /**
     * Case Guarantee Disposition.
     *
     * @var string
     */
    private $caseGuaranteeDisposition = 'td.col-guarantee-disposition';

    /**
     * Case Review Disposition.
     *
     * @var string
     */
    private $caseReviewDisposition = 'td.col-case-review';

    /**
     * Get Case Status information.
     *
     * @return string
     */
    public function getCaseStatus()
    {
        return $this->_rootElement->find($this->caseStatus)->getText();
    }

    /**
     * Get Case Guarantee Disposition status.
     *
     * @return string
     */
    public function getCaseGuaranteeDisposition()
    {
        return $this->_rootElement->find($this->caseGuaranteeDisposition)->getText();
    }

    /**
     * Get Case Review Disposition status.
     *
     * @return string
     */
    public function getCaseReviewDisposition()
    {
        return $this->_rootElement->find($this->caseReviewDisposition)->getText();
    }
}

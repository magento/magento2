<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Case Guarantee Disposition.
     *
     * @var string
     */
    private $caseGuaranteeDisposition = 'td.col-guarantee-disposition';

    /**
     * Get Case Guarantee Disposition status.
     *
     * @return string
     */
    public function getCaseGuaranteeDisposition()
    {
        return $this->_rootElement->find($this->caseGuaranteeDisposition)->getText();
    }
}

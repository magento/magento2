<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Shipment;

use Mtf\Block\Block;

/**
 * Class Totals
 * Shipment totals block
 *
 */
class Totals extends Block
{
    /**
     * Submit Shipment selector
     *
     * @var string
     */
    protected $submit = '[data-ui-id="order-items-submit-button"]';

    /**
     * Ship order
     */
    public function submit()
    {
        $this->_rootElement->find($this->submit)->click();
    }
}

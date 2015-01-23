<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Block\Adminhtml\Order\Tracking;

use Magento\Mtf\Block\Form;

/**
 * Class Item
 * Item tracking to ship block
 */
class Item extends Form
{
    /**
     * Fill item tracking
     *
     * @param array $fields
     * @return void
     */
    public function fillRow(array $fields)
    {
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping);
    }
}

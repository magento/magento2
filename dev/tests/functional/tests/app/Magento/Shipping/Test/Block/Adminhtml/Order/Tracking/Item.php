<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Shipping\Test\Block\Adminhtml\Order\Tracking;

use Mtf\Block\Form;

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

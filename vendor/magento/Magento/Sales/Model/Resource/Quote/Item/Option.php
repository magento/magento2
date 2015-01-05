<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Model\Resource\Quote\Item;

/**
 * Item option resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Option extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Main table and field initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_quote_item_option', 'option_id');
    }
}

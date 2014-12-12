<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Totals;

use Magento\Store\Model\Resource\Website\Collection;

/**
 * Adminhtml sales order create totals table block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Table extends \Magento\Backend\Block\Template
{
    /**
     * Website collection
     *
     * @var Collection|null
     */
    protected $_websiteCollection = null;

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_totals_table');
    }
}

<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Totals;

use Magento\Store\Model\ResourceModel\Website\Collection;

/**
 * Adminhtml sales order create totals table block
 */
class Table extends \Magento\Backend\Block\Template
{
    /**
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

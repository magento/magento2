<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\ResourceModel;

/**
 * Resource model for Product Frontend Action
 */
class ProductFrontendAction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_frontend_action', 'action_id');
    }
}

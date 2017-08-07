<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

/**
 * Resource model for Product Frontend Action
 * @since 2.2.0
 */
class ProductFrontendAction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.2.0
     */
    protected function _construct()
    {
        $this->_init('catalog_product_frontend_action', 'action_id');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Design;

/**
 * Config Design resource model
 * @since 2.1.0
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.1.0
     */
    protected function _construct()
    {
        $this->_init('design_config_grid_flat', 'entity_id');
    }
}

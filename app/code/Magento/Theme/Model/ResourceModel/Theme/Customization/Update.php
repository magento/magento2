<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Customization;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Theme customization link resource model
 */
class Update extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('theme_file_update', 'file_update_id');
    }
}

<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Model\ResourceModel\Theme\Customization;

/**
 * Theme customization link resource model
 */
class Update extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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

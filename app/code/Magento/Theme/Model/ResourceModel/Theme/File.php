<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\ResourceModel\Theme;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Theme files resource model
 */
class File extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('theme_file', 'theme_files_id');
    }
}

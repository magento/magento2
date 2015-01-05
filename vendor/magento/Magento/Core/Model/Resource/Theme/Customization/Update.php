<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Core\Model\Resource\Theme\Customization;

/**
 * Theme customization link resource model
 */
class Update extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_theme_file_update', 'file_update_id');
    }
}

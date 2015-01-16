<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Layout;

/**
 * Layout Link model class
 *
 * @method int getStoreId()
 * @method int getThemeId()
 * @method int getLayoutUpdateId()
 * @method \Magento\Core\Model\Layout\Link setStoreId($id)
 * @method \Magento\Core\Model\Layout\Link setThemeId($id)
 * @method \Magento\Core\Model\Layout\Link setLayoutUpdateId($id)
 */
class Link extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Layout Update model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Resource\Layout\Link');
    }
}

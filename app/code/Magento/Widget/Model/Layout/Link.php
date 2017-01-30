<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Layout;

/**
 * Layout Link model class
 *
 * @method int getStoreId()
 * @method int getThemeId()
 * @method int getLayoutUpdateId()
 * @method \Magento\Widget\Model\Layout\Link setStoreId($id)
 * @method \Magento\Widget\Model\Layout\Link setThemeId($id)
 * @method \Magento\Widget\Model\Layout\Link setLayoutUpdateId($id)
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
        $this->_init('Magento\Widget\Model\ResourceModel\Layout\Link');
    }
}

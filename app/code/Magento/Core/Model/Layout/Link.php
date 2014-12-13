<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

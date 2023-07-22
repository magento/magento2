<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Layout;

use Magento\Framework\Model\AbstractModel;
use Magento\Widget\Model\ResourceModel\Layout\Link as ResourceLink;

/**
 * Layout Link model class
 *
 * @method int getStoreId()
 * @method int getThemeId()
 * @method int getLayoutUpdateId()
 * @method Link setStoreId($id)
 * @method Link setThemeId($id)
 * @method Link setLayoutUpdateId($id)
 */
class Link extends AbstractModel
{
    /**
     * Layout Update model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceLink::class);
    }
}

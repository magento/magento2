<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\ResourceModel\SourceStockLink;

use Magento\Inventory\Model\ResourceModel\SourceStockLink as ResourceSourceStockLink;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\SourceStockLink as SourceStockLinkModel;

/**
 * Resource Collection of SourceStockLink entities
 * It is not an API because SourceStockLink must be loaded via Source entity only
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SourceStockLinkModel::class, ResourceSourceStockLink::class);
    }

    /**
     * Id field name getter
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return 'link_id';
    }
}

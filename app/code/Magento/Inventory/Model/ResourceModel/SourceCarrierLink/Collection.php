<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\ResourceModel\SourceCarrierLink;

use Magento\Inventory\Model\ResourceModel\SourceCarrierLink as ResourceSourceCarrierLink;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\SourceCarrierLink as SourceCarrierLinkModel;

/**
 * Resource Collection of SourceCarrierLink entities
 * It is not an API because SourceCarrierLink must be loaded via Source entity only
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SourceCarrierLinkModel::class, ResourceSourceCarrierLink::class);
    }

    /**
     * Id field name getter
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return 'source_carrier_link_id';
    }
}

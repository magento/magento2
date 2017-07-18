<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel\SourceStockLink;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\ResourceModel\SourceStockLink as SourceStockLinkResourceModel;
use Magento\Inventory\Model\SourceStockLink as SourceStockLinkModel;

/**
 * Resource Collection of SourceStockLink entities
 *
 * @api
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SourceStockLinkModel::class, SourceStockLinkResourceModel::class);
    }
}

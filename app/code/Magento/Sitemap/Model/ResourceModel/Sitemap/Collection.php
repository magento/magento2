<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sitemap\Model\ResourceModel\Sitemap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sitemap\Model\ResourceModel\Sitemap as ResourceSitemap;
use Magento\Sitemap\Model\Sitemap as ModelSitemap;

/**
 * Sitemap resource model collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @api
 * @since 100.0.2
 */
class Collection extends AbstractCollection
{
    /**
     * Init collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ModelSitemap::class, ResourceSitemap::class);
    }

    /**
     * Filter collection by specified store ids
     *
     * @param array|int[] $storeIds
     * @return $this
     */
    public function addStoreFilter($storeIds)
    {
        $this->getSelect()->where('main_table.store_id IN (?)', $storeIds);
        return $this;
    }
}

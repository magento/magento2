<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * @api
 * @since 2.1.0
 */
class SynonymGroup extends AbstractDb
{
    /**
     * Get synonym groups by scope
     *
     * @param int $websiteId
     * @param int $storeId
     * @return string[]
     * @since 2.1.0
     */
    public function getByScope($websiteId, $storeId)
    {
        $websiteIdField = $this->getConnection()
            ->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'website_id'));
        $storeIdField = $this->getConnection()
            ->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), 'store_id'));
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), ['group_id', 'synonyms'])
            ->where($websiteIdField . '=?', $websiteId)
            ->where($storeIdField . '=?', $storeId);
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * Init resource data
     *
     * @return void
     * @since 2.1.0
     */
    protected function _construct()
    {
        $this->_init('search_synonyms', 'group_id');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report event collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Event;

/**
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store Ids
     *
     * @var array
     * @since 2.0.0
     */
    protected $_storeIds;

    /**
     * Resource initializations
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Reports\Model\Event::class, \Magento\Reports\Model\ResourceModel\Event::class);
    }

    /**
     * Add store ids filter
     * @codeCoverageIgnore
     *
     * @param array $storeIds
     * @return $this
     * @since 2.0.0
     */
    public function addStoreFilter(array $storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * Add recently filter
     *
     * @param int $typeId
     * @param int $subjectId
     * @param int $subtype
     * @param null|int|array $ignore
     * @param int $limit
     * @return $this
     * @since 2.0.0
     */
    public function addRecentlyFiler($typeId, $subjectId, $subtype = 0, $ignore = null, $limit = 15)
    {
        $stores = $this->getResource()->getCurrentStoreIds($this->_storeIds);
        $select = $this->getSelect();
        $select->where(
            'event_type_id = ?',
            $typeId
        )->where(
            'subject_id = ?',
            $subjectId
        )->where(
            'subtype = ?',
            $subtype
        )->where(
            'store_id IN(?)',
            $stores
        );
        if ($ignore) {
            if (is_array($ignore)) {
                $select->where('object_id NOT IN(?)', $ignore);
            } else {
                $select->where('object_id <> ?', $ignore);
            }
        }
        $select->group('object_id')->limit($limit);
        return $this;
    }
}

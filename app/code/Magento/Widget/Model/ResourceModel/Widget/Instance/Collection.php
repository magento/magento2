<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model\ResourceModel\Widget\Instance;

/**
 * Widget Instance Collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Fields map for corellation names & real selected fields
     *
     * @var array
     * @since 2.0.0
     */
    protected $_map = ['fields' => ['type' => 'instance_type']];

    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Magento\Widget\Model\Widget\Instance::class,
            \Magento\Widget\Model\ResourceModel\Widget\Instance::class
        );
    }

    /**
     * Filter by store ids
     *
     * @param array|int $storeIds
     * @param bool $withDefaultStore if TRUE also filter by store id '0'
     * @return $this
     * @since 2.0.0
     */
    public function addStoreFilter($storeIds = [], $withDefaultStore = true)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }
        if ($withDefaultStore && !in_array('0', $storeIds)) {
            array_unshift($storeIds, 0);
        }
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = $this->_getConditionSql('store_ids', ['finset' => $storeId]);
        }

        $this->_select->where(implode(' OR ', $where));

        return $this;
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Widget Instance Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Model\ResourceModel\Widget\Instance;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Fields map for corellation names & real selected fields
     *
     * @var array
     */
    protected $_map = ['fields' => ['type' => 'instance_type']];

    /**
     * Constructor
     *
     * @return void
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

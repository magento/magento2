<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Group;

/**
 * Customer group collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Customer\Model\Group::class, \Magento\Customer\Model\ResourceModel\Group::class);
    }

    /**
     * Set ignore ID filter
     *
     * @param array $indexes
     * @return $this
     * @since 2.0.0
     */
    public function setIgnoreIdFilter($indexes)
    {
        if (count($indexes)) {
            $this->addFieldToFilter('main_table.customer_group_id', ['nin' => $indexes]);
        }
        return $this;
    }

    /**
     * Set real groups filter
     *
     * @return $this
     * @since 2.0.0
     */
    public function setRealGroupsFilter()
    {
        return $this->addFieldToFilter('customer_group_id', ['gt' => 0]);
    }

    /**
     * Add tax class
     *
     * @return $this
     * @since 2.0.0
     */
    public function addTaxClass()
    {
        $this->getSelect()->joinLeft(
            ['tax_class_table' => $this->getTable('tax_class')],
            "main_table.tax_class_id = tax_class_table.class_id"
        );
        return $this;
    }

    /**
     * Retrieve option array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('customer_group_id', 'customer_group_code');
    }

    /**
     * Retrieve option hash
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionHash()
    {
        return parent::_toOptionHash('customer_group_id', 'customer_group_code');
    }
}

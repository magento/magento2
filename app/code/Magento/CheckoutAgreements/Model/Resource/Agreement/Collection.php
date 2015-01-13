<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model\Resource\Agreement;

/**
 * Resource Model for Agreement Collection
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * @var array
     */
    protected $_map = ['fields' => ['agreement_id' => 'main_table.agreement_id']];

    /**
     * Is store filter with admin store
     *
     * @var bool
     */
    protected $_isStoreFilterWithAdmin = true;

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CheckoutAgreements\Model\Agreement', 'Magento\CheckoutAgreements\Model\Resource\Agreement');
    }

    /**
     * Filter collection by specified store ids
     *
     * @param int|\Magento\Store\Model\Store $store
     * @return $this
     */
    public function addStoreFilter($store)
    {
        // check and prepare data
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        } elseif (is_numeric($store)) {
            $store = [$store];
        }

        $alias = 'store_table_' . implode('_', $store);
        if ($this->getFlag($alias)) {
            return $this;
        }

        $storeFilter = [$store];
        if ($this->_isStoreFilterWithAdmin) {
            $storeFilter[] = 0;
        }

        // add filter
        $this->getSelect()->join(
            [$alias => $this->getTable('checkout_agreement_store')],
            'main_table.agreement_id = ' . $alias . '.agreement_id',
            []
        )->where(
            $alias . '.store_id IN (?)',
            $storeFilter
        )->group(
            'main_table.agreement_id'
        );

        $this->setFlag($alias, true);
        return $this;
    }

    /**
     * Make store filter using admin website or not
     *
     * @param bool $value
     * @return $this
     */
    public function setIsStoreFilterWithAdmin($value)
    {
        $this->_isStoreFilterWithAdmin = (bool)$value;
        return $this;
    }
}

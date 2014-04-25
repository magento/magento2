<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_map = array('fields' => array('agreement_id' => 'main_table.agreement_id'));

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
            $store = array($store->getId());
        } elseif (is_numeric($store)) {
            $store = array($store);
        }

        $alias = 'store_table_' . implode('_', $store);
        if ($this->getFlag($alias)) {
            return $this;
        }

        $storeFilter = array($store);
        if ($this->_isStoreFilterWithAdmin) {
            $storeFilter[] = 0;
        }

        // add filter
        $this->getSelect()->join(
            array($alias => $this->getTable('checkout_agreement_store')),
            'main_table.agreement_id = ' . $alias . '.agreement_id',
            array()
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

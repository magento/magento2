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
namespace Magento\Customer\Model\Resource\Group;

/**
 * Customer group collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Customer\Model\Group', 'Magento\Customer\Model\Resource\Group');
    }

    /**
     * Set ignore ID filter
     *
     * @param array $indexes
     * @return $this
     */
    public function setIgnoreIdFilter($indexes)
    {
        if (count($indexes)) {
            $this->addFieldToFilter('main_table.customer_group_id', array('nin' => $indexes));
        }
        return $this;
    }

    /**
     * Set real groups filter
     *
     * @return $this
     */
    public function setRealGroupsFilter()
    {
        return $this->addFieldToFilter('customer_group_id', array('gt' => 0));
    }

    /**
     * Add tax class
     *
     * @return $this
     */
    public function addTaxClass()
    {
        $this->getSelect()->joinLeft(
            array('tax_class_table' => $this->getTable('tax_class')),
            "main_table.tax_class_id = tax_class_table.class_id"
        );
        return $this;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('customer_group_id', 'customer_group_code');
    }

    /**
     * Retrieve option hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return parent::_toOptionHash('customer_group_id', 'customer_group_code');
    }
}

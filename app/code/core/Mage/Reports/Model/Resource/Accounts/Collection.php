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
 * @category    Mage
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * New Accounts Report collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Accounts_Collection extends Mage_Reports_Model_Resource_Customer_Collection
{

    /**
     * Join created_at and accounts fields
     *
     * @param string $from
     * @param string $to
     * @return Mage_Reports_Model_Resource_Accounts_Collection
     */
    protected function _joinFields($from = '', $to = '')
    {

        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to, 'datetime' => true))
             ->addExpressionAttributeToSelect('accounts', 'COUNT({{entity_id}})', array('entity_id'));

        $this->getSelect()->having("{$this->_joinFields['accounts']['field']} > ?", 0);

        return $this;
    }

    /**
     * Set date range
     *
     * @param string $from
     * @param string $to
     * @return Mage_Reports_Model_Resource_Accounts_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
             ->_joinFields($from, $to);
        return $this;
    }

    /**
     * Set store ids to final result
     *
     * @param array $storeIds
     * @return Mage_Reports_Model_Resource_Accounts_Collection
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->addAttributeToFilter('store_id', array('in' => (array)$storeIds));
        }
        return $this;
    }
}

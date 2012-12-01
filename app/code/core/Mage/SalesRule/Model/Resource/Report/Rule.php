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
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Rule report resource model
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Resource_Report_Rule extends Mage_Reports_Model_Resource_Report_Abstract
{
    /**
     * Resource Report Rule constructor
     *
     */
    protected function _construct()
    {
        $this->_setResource('salesrule');
    }

    /**
     * Aggregate Coupons data
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_SalesRule_Model_Resource_Report_Rule
     */
    public function aggregate($from = null, $to = null)
    {
        Mage::getResourceModel('Mage_SalesRule_Model_Resource_Report_Rule_Createdat')->aggregate($from, $to);
        Mage::getResourceModel('Mage_SalesRule_Model_Resource_Report_Rule_Updatedat')->aggregate($from, $to);
        $this->_setFlagData(Mage_Reports_Model_Flag::REPORT_COUPONS_FLAG_CODE);

        return $this;
    }

    /**
     * Get all unique Rule Names from aggregated coupons usage data
     *
     * @return array
     */
    public function getUniqRulesNamesList()
    {
        $adapter = $this->_getReadAdapter();
        $tableName = $this->getTable('coupon_aggregated');
        $select = $adapter->select()
            ->from(
                $tableName,
                new Zend_Db_Expr('DISTINCT rule_name')
            )
            ->where('rule_name IS NOT NULL')
            ->where('rule_name <> ?', '')
            ->order('rule_name ASC');

        $rulesNames = $adapter->fetchAll($select);

        $result = array();

        foreach ($rulesNames as $row) {
            $result[] = $row['rule_name'];
        }

        return $result;
    }
}

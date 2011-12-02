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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * SalesRule Model Resource Rule_Collection
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Resource_Rule_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_SalesRule_Model_Rule', 'Mage_SalesRule_Model_Resource_Rule');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }

    /**
     * Set filter to select rules that matches current criteria
     *
     * @param unknown_type $websiteId
     * @param unknown_type $customerGroupId
     * @param unknown_type $couponCode
     * @param unknown_type $now
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    public function setValidationFilter($websiteId, $customerGroupId, $couponCode = '', $now = null)
    {
        if (is_null($now)) {
            $now = Mage::getModel('Mage_Core_Model_Date')->date('Y-m-d');
        }
        /* We need to overwrite joinLeft if coupon is applied */
        $this->getSelect()->reset();
        parent::_initSelect();

        $this->addFieldToFilter('website_ids', array('finset' => (int)$websiteId))
            ->addFieldToFilter('customer_group_ids', array('finset' => (int)$customerGroupId))
            ->addFieldToFilter('is_active', 1);

        if ($couponCode) {
            $this->getSelect()
                ->joinLeft(
                    array('rule_coupons' => $this->getTable('salesrule_coupon')),
                    'main_table.rule_id = rule_coupons.rule_id ',
                    array('code')
                );
            $this->getSelect()->where(
                '(main_table.coupon_type = ? ',  Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON
            );
            $this->getSelect()->orWhere('rule_coupons.code = ?)', $couponCode);
        } else {
            $this->addFieldToFilter('main_table.coupon_type', Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON);
        }
        $this->getSelect()->where('from_date is null or from_date <= ?', $now);
        $this->getSelect()->where('to_date is null or to_date >= ?', $now);
        $this->getSelect()->order('sort_order');
        return $this;
    }

    /**
     * Filter collection by specified website IDs
     *
     * @param int|array $websiteIds
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    public function addWebsiteFilter($websiteIds)
    {
        if (!is_array($websiteIds)) {
            $websiteIds = array($websiteIds);
        }
        $parts = array();
        foreach ($websiteIds as $websiteId) {
            $parts[] = $this->getConnection()
                ->prepareSqlCondition('main_table.website_ids', array('finset' => $websiteId));
        }
        if ($parts) {
            $this->getSelect()->where(new Zend_Db_Expr(implode(' OR ', $parts)));
        }
        return $this;
    }

    /**
     * Init collection select
     *
     *
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                array('rule_coupons' => $this->getTable('salesrule_coupon')),
                'main_table.rule_id = rule_coupons.rule_id AND rule_coupons.is_primary = 1',
                array('code')
            );
        return $this;
    }

    /**
     * Find product attribute in conditions or actions
     *
     * @param string $attributeCode
     * @return Mage_SalesRule_Model_Resource_Rule_Collection
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $match = sprintf('%%%s%%', substr(serialize(array('attribute' => $attributeCode)), 5, -1));
        $field = $this->_getMappedField('conditions_serialized');
        $cCond = $this->_getConditionSql($field, array('like' => $match));
        $field = $this->_getMappedField('actions_serialized');
        $aCond = $this->_getConditionSql($field, array('like' => $match));

        $this->getSelect()->where(sprintf('(%s OR %s)', $cCond, $aCond), null, Varien_Db_Select::TYPE_CONDITION);

        return $this;
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Resource\Calculation;

/**
 * Tax rate resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Rule extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('tax_calculation_rule', 'tax_calculation_rule_id');
    }

    /**
     * Initialize unique fields
     *
     * @return \Magento\Tax\Model\Resource\Calculation\Rule
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [['field' => ['code'], 'title' => __('Code')]];
        return $this;
    }

    /**
     * Fetches rules by rate, customer tax classes and product tax classes.  Returns array of rule codes.
     *
     * @param array $rateId
     * @param array $customerTaxClassIds
     * @param array $productTaxClassIds
     * @return array
     */
    public function fetchRuleCodes($rateId, $customerTaxClassIds, $productTaxClassIds)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from(['main' => $this->getTable('tax_calculation')], null)
            ->joinLeft(
                ['d' => $this->getTable('tax_calculation_rule')],
                'd.tax_calculation_rule_id = main.tax_calculation_rule_id',
                ['d.code']
            )
            ->where('main.tax_calculation_rate_id in (?)', $rateId)
            ->where('main.customer_tax_class_id in (?)', $customerTaxClassIds)
            ->where('main.product_tax_class_id in (?)', $productTaxClassIds)
            ->distinct(true);

        return $adapter->fetchCol($select);
    }
}

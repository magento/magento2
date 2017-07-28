<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\ResourceModel\Calculation;

/**
 * Tax rate resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('tax_calculation_rule', 'tax_calculation_rule_id');
    }

    /**
     * Initialize unique fields
     *
     * @return \Magento\Tax\Model\ResourceModel\Calculation\Rule
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function fetchRuleCodes($rateId, $customerTaxClassIds, $productTaxClassIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
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

        return $connection->fetchCol($select);
    }
}

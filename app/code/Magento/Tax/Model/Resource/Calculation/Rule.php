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
        $this->_uniqueFields = array(array('field' => array('code'), 'title' => __('Code')));
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
            ->from(array('main' => $this->getTable('tax_calculation')), null)
            ->joinLeft(
                array('d' => $this->getTable('tax_calculation_rule')),
                'd.tax_calculation_rule_id = main.tax_calculation_rule_id',
                array('d.code')
            )
            ->where('main.tax_calculation_rate_id in (?)', $rateId)
            ->where('main.customer_tax_class_id in (?)', $customerTaxClassIds)
            ->where('main.product_tax_class_id in (?)', $productTaxClassIds)
            ->distinct(true);

        return $adapter->fetchCol($select);
    }
}

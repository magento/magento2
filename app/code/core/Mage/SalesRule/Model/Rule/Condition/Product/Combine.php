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


class Mage_SalesRule_Model_Rule_Condition_Product_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('Mage_SalesRule_Model_Rule_Condition_Product_Combine');
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('Mage_SalesRule_Model_Rule_Condition_Product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        foreach ($productAttributes as $code=>$label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = array(
                    'value' => 'Mage_SalesRule_Model_Rule_Condition_Product|' . $code, 'label' => $label
                );
            } else {
                $pAttributes[] =
                    array('value' => 'Mage_SalesRule_Model_Rule_Condition_Product|' . $code, 'label' => $label);
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value' => 'Mage_SalesRule_Model_Rule_Condition_Product_Combine',
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Conditions Combination')
            ),
            array('label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Cart Item Attribute'),
                'value' => $iAttributes
            ),
            array('label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Product Attribute'),
                'value' => $pAttributes
            ),
        ));
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}

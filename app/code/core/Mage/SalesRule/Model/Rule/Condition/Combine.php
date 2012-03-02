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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_SalesRule_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('Mage_SalesRule_Model_Rule_Condition_Combine');
    }

    public function getNewChildSelectOptions()
    {
        $addressCondition = Mage::getModel('Mage_SalesRule_Model_Rule_Condition_Address');
        $addressAttributes = $addressCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($addressAttributes as $code=>$label) {
            $attributes[] = array(
                'value' => 'Mage_SalesRule_Model_Rule_Condition_Address|' . $code, 'label' => $label
            );
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array('value' => 'Mage_SalesRule_Model_Rule_Condition_Product_Found',
                'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Product attribute combination')
            ),
            array('value' => 'Mage_SalesRule_Model_Rule_Condition_Product_Subselect',
                'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Products subselection')
            ),
            array('value' => 'Mage_SalesRule_Model_Rule_Condition_Combine',
                'label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Conditions combination')
            ),
            array('label' => Mage::helper('Mage_SalesRule_Helper_Data')->__('Cart Attribute'), 'value' => $attributes),
        ));

        $additional = new Varien_Object();
        Mage::dispatchEvent('salesrule_rule_condition_combine', array('additional' => $additional));
        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}

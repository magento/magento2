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


class Mage_SalesRule_Model_Rule_Condition_Product_Subselect
    extends Mage_SalesRule_Model_Rule_Condition_Product_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('Mage_SalesRule_Model_Rule_Condition_Product_Subselect')
            ->setValue(null);
    }

    public function loadArray($arr, $key='conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function asXml($containerKey='conditions', $itemKey='condition')
    {
        $xml .= '<attribute>'.$this->getAttribute().'</attribute>'
            .'<operator>'.$this->getOperator().'</operator>'
            .parent::asXml($containerKey, $itemKey);
        return $xml;
    }

//    public function loadAggregatorOptions()
//    {
//        $this->setAggregatorOption(array(
//            '1/all' => Mage::helper('Mage_Rule_Helper_Data')->__('MATCHING ALL'),
//            '1/any' => Mage::helper('Mage_Rule_Helper_Data')->__('MATCHING ANY'),
//            '0/all' => Mage::helper('Mage_Rule_Helper_Data')->__('NOT MATCHING ALL'),
//            '0/any' => Mage::helper('Mage_Rule_Helper_Data')->__('NOT MATCHING ANY'),
//        ));
//        return $this;
//    }

    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('Mage_SalesRule_Helper_Data');
        $this->setAttributeOption(array(
            'qty'  => $hlp->__('total quantity'),
            'base_row_total'  => $hlp->__('total amount'),
        ));
        return $this;
    }

    public function loadValueOptions()
    {
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => Mage::helper('Mage_Rule_Helper_Data')->__('is'),
            '!='  => Mage::helper('Mage_Rule_Helper_Data')->__('is not'),
            '>='  => Mage::helper('Mage_Rule_Helper_Data')->__('equals or greater than'),
            '<='  => Mage::helper('Mage_Rule_Helper_Data')->__('equals or less than'),
            '>'   => Mage::helper('Mage_Rule_Helper_Data')->__('greater than'),
            '<'   => Mage::helper('Mage_Rule_Helper_Data')->__('less than'),
            '()'  => Mage::helper('Mage_Rule_Helper_Data')->__('is one of'),
            '!()' => Mage::helper('Mage_Rule_Helper_Data')->__('is not one of'),
        ));
        return $this;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml().
            Mage::helper('Mage_SalesRule_Helper_Data')->__("If %s %s %s for a subselection of items in cart matching %s of these conditions:",
              $this->getAttributeElement()->getHtml(),
              $this->getOperatorElement()->getHtml(),
              $this->getValueElement()->getHtml(),
              $this->getAggregatorElement()->getHtml()
           );
           if ($this->getId()!='1') {
               $html.= $this->getRemoveLinkHtml();
           }
        return $html;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        if (!$this->getConditions()) {
            return false;
        }

//        $value = $this->getValue();
//        $aggregatorArr = explode('/', $this->getAggregator());
//        $this->setValue((int)$aggregatorArr[0])->setAggregator($aggregatorArr[1]);

        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getQuote()->getAllItems() as $item) {
            if (parent::validate($item)) {
                $total += $item->getData($attr);
            }
        }
//        $this->setAggregator(join('/', $aggregatorArr))->setValue($value);

        return $this->validateAttribute($total);
    }
}

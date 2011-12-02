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
 * @package     Mage_CatalogRule
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_CatalogRule_Model_Rule_Action_Product extends Mage_Rule_Model_Action_Abstract
{
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array(
            'rule_price'=>Mage::helper('Mage_CatalogInventory_Helper_Data')->__('Rule price'),
        ));
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            'to_fixed'=>Mage::helper('Mage_CatalogInventory_Helper_Data')->__('To Fixed Value'),
            'to_percent'=>Mage::helper('Mage_CatalogInventory_Helper_Data')->__('To Percentage'),
            'by_fixed'=>Mage::helper('Mage_CatalogInventory_Helper_Data')->__('By Fixed value'),
            'by_percent'=>Mage::helper('Mage_CatalogInventory_Helper_Data')->__('By Percentage'),
        ));
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml().Mage::helper('Mage_CatalogRule_Helper_Data')->__("Update product's %s %s: %s", $this->getAttributeElement()->getHtml(), $this->getOperatorElement()->getHtml(), $this->getValueElement()->getHtml());
        $html.= $this->getRemoveLinkHtml();
        return $html;
    }
}

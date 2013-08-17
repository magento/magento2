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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product gallery
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_TemplateSelector extends Mage_Core_Block_Template
{
    /**
     * Retrieve list of product templates with search part contained in label
     *
     * @param string $labelPart
     * @return array
     */
    public function getSuggestedTemplates($labelPart)
    {
        $product = Mage::registry('product');
        $entityType = $product->getResource()->getEntityType();
        $labelPart = Mage::getResourceHelper('Mage_Core')->addLikeEscape($labelPart, array('position' => 'any'));
        $collection = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
            ->setEntityTypeFilter($entityType->getId())
            ->addFieldToFilter('attribute_set_name', array('like' => $labelPart))
            ->addFieldToSelect('attribute_set_id', 'id')
            ->addFieldToSelect('attribute_set_name', 'label')
            ->setOrder('attribute_set_name', Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection::SORT_ORDER_ASC);
        return $collection->getData();
    }
}

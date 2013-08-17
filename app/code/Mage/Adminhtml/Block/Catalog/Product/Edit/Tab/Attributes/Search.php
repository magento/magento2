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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * New attribute panel on product edit page
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes_Search extends Mage_Backend_Block_Widget
{
    /**
     * Define block template
     */
    protected function _construct()
    {
        $this->setTemplate('Mage_Catalog::product/edit/attribute/search.phtml');
        parent::_construct();
    }

    /**
     * @return array
     */
    public function getSelectorOptions()
    {
        $templateId = Mage::registry('product')->getAttributeSetId();
        return array(
            'source' => $this->getUrl('*/catalog_product/suggestAttributes'),
            'minLength' => 0,
            'ajaxOptions' => array('data' => array('template_id' => $templateId)),
            'template' => '[data-template-for="product-attribute-search"]',
            'data' => $this->getSuggestedAttributes('', $templateId),
        );
    }

    /**
     * Retrieve list of attributes with admin store label containing $labelPart
     *
     * @param string $labelPart
     * @param int $templateId
     * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
     */
    public function getSuggestedAttributes($labelPart, $templateId = null)
    {
        $escapedLabelPart = Mage::getResourceHelper('Mage_Core')->addLikeEscape($labelPart, array('position' => 'any'));
        /** @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
        $collection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Attribute_Collection')
            ->addFieldToFilter('frontend_label', array('like' => $escapedLabelPart));

        $collection->setExcludeSetFilter($templateId ?: $this->getRequest()->getParam('template_id'))->setPageSize(20);

        $result = array();
        foreach ($collection->getItems() as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $result[] = array(
                'id'      => $attribute->getId(),
                'label'   => $attribute->getFrontendLabel(),
                'code'    => $attribute->getAttributeCode(),
            );
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getAddAttributeUrl()
    {
        return $this->getUrl('*/catalog_product/addAttributeToTemplate');
    }
}

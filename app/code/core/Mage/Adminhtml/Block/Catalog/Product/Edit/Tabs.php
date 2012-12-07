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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * admin product edit tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    protected $_attributeTabBlock = 'Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes';

    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_info_tabs');
        $this->setDestElementId('product-edit-form-tabs');
        $this->setTitle(Mage::helper('Mage_Catalog_Helper_Data')->__('Product Information'));
    }

    protected function _prepareLayout()
    {
        $product = $this->getProduct();

        if (!($setId = $product->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }

        if ($setId) {
            $groupCollection = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection')
                ->setAttributeSetFilter($setId)
                ->setSortOrder()
                ->load();

            $tabAttributesBlock = $this->getLayout()->createBlock(
                $this->getAttributeTabBlock(), $this->getNameInLayout() . '_attributes_tab'
            );
            foreach ($groupCollection as $group) {
                $attributes = $product->getAttributes($group->getId(), true);
                // do not add groups without attributes

                foreach ($attributes as $key => $attribute) {
                    if( !$attribute->getIsVisible() ) {
                        unset($attributes[$key]);
                    }
                }

                if (count($attributes)==0) {
                    continue;
                }

                $this->addTab('group_' . $group->getId(), array(
                    'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__($group->getAttributeGroupName()),
                    'content'   => $this->_translateHtml($tabAttributesBlock->setGroup($group)
                        ->setGroupAttributes($attributes)
                        ->toHtml()
                    ),
                ));
            }

            if (Mage::helper('Mage_Core_Helper_Data')->isModuleEnabled('Mage_CatalogInventory')) {
                $this->addTab('inventory', array(
                    'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Inventory'),
                    'content'   => $this->_translateHtml($this->getLayout()
                        ->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory')->toHtml()),
                ));
            }

            /**
             * Don't display website tab for single mode
             */
            if (!Mage::app()->isSingleStoreMode()) {
                $this->addTab('websites', array(
                    'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Websites'),
                    'content'   => $this->_translateHtml($this->getLayout()
                        ->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Websites')->toHtml()),
                ));
            }

            $this->addTab('related', array(
                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Related Products'),
                'url'       => $this->getUrl('*/*/related', array('_current' => true)),
                'class'     => 'ajax',
            ));

            $this->addTab('upsell', array(
                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Up-sells'),
                'url'       => $this->getUrl('*/*/upsell', array('_current' => true)),
                'class'     => 'ajax',
            ));

            $this->addTab('crosssell', array(
                'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Cross-sells'),
                'url'       => $this->getUrl('*/*/crosssell', array('_current' => true)),
                'class'     => 'ajax',
            ));

            $alertPriceAllow = Mage::getStoreConfig('catalog/productalert/allow_price');
            $alertStockAllow = Mage::getStoreConfig('catalog/productalert/allow_stock');

            if (($alertPriceAllow || $alertStockAllow) && !$product->isGrouped()) {
                $this->addTab('productalert', array(
                    'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Product Alerts'),
                    'content'   => $this->_translateHtml($this->getLayout()
                        ->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Alerts', 'admin.alerts.products')
                        ->toHtml()
                    )
                ));
            }

            if( $this->getRequest()->getParam('id', false) ) {
                if (Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_Review')) {
                    if (Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Review::reviews_ratings')){
                        $this->addTab('reviews', array(
                            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Product Reviews'),
                            'url'   => $this->getUrl('*/*/reviews', array('_current' => true)),
                            'class' => 'ajax',
                        ));
                    }
                }
            }

            /**
             * Do not change this tab id
             * @see Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs_Configurable
             * @see Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tabs
             */
            if (!$product->isGrouped()) {
                $this->addTab('customer_options', array(
                    'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Custom Options'),
                    'url'   => $this->getUrl('*/*/options', array('_current' => true)),
                    'class' => 'ajax',
                ));
            }

        }
        return parent::_prepareLayout();
    }

    /**
     * Retrive product object from object if not from registry
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!($this->getData('product') instanceof Mage_Catalog_Model_Product)) {
            $this->setData('product', Mage::registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        if (is_null(Mage::helper('Mage_Adminhtml_Helper_Catalog')->getAttributeTabBlock())) {
            return $this->_attributeTabBlock;
        }
        return Mage::helper('Mage_Adminhtml_Helper_Catalog')->getAttributeTabBlock();
    }

    public function setAttributeTabBlock($attributeTabBlock)
    {
        $this->_attributeTabBlock = $attributeTabBlock;
        return $this;
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        Mage::getSingleton('Mage_Core_Model_Translate_Inline')->processResponseBody($html);
        return $html;
    }
}

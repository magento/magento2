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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml product attribute edit page tabs
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('product_attribute_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main', array(
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Properties'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Properties'),
            'content'   => $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main')->toHtml(),
            'active'    => true
        ));

        $this->addTab('labels', array(
            'label'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Manage Label / Options'),
            'title'     => Mage::helper('Mage_Catalog_Helper_Data')->__('Manage Label / Options'),
            'content'   => $this->getLayout()
                ->createBlock('Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}

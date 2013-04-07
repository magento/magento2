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
 * Products grid for URL rewrites editing
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Urlrewrite_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
    /**
     * Disable massaction
     *
     * @return Mage_Adminhtml_Block_Urlrewrite_Catalog_Product_Grid
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Prepare columns layout
     *
     * @return Mage_Adminhtml_Block_Urlrewrite_Catalog_Product_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('Mage_Adminhtml_Helper_Data')->__('ID'),
                'width' => 50,
                'index' => 'entity_id',
        ));

        $this->addColumn('name',
            array(
                'header'=> Mage::helper('Mage_Adminhtml_Helper_Data')->__('Name'),
                'index' => 'name',
        ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('Mage_Adminhtml_Helper_Data')->__('SKU'),
                'width' => 80,
                'index' => 'sku',
        ));
        $this->addColumn('status',
            array(
                'header'=> Mage::helper('Mage_Adminhtml_Helper_Data')->__('Status'),
                'width' => 50,
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getOptionArray(),
        ));
        return $this;
    }

    /**
     * Get URL for dispatching grid ajax requests
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGrid', array('_current' => true));
    }

    /**
     * Return row url for js event handlers
     *
     * @param Mage_Catalog_Model_Product|Varien_Object
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('product' => $row->getId())) . 'category';
    }
}

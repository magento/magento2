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
 * Adminhtml customer view wishlist block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_View_Wishlist extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initial settings
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_view_wishlist_grid');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setEmptyText(Mage::helper('Mage_Customer_Helper_Data')->__("There are no items in customer's wishlist at the moment"));
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_View_Wishlist
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Mage_Wishlist_Model_Item')->getCollection()
            ->addCustomerIdFilter(Mage::registry('current_customer')->getId())
            ->addDaysInWishlist()
            ->addStoreData()
            ->setInStockFilter(true);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Customer_Edit_Tab_View_Wishlist
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Product ID'),
            'index'     => 'product_id',
            'type'      => 'number',
            'width'     => '100px'
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Product Name'),
            'index'     => 'product_name',
            'renderer'  => 'Mage_Adminhtml_Block_Customer_Edit_Tab_View_Grid_Renderer_Item'
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store', array(
                'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Added From'),
                'index'     => 'store_id',
                'type'      => 'store',
                'width'     => '160px',
            ));
        }

        $this->addColumn('added_at', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Date Added'),
            'index'     => 'added_at',
            'type'      => 'date',
            'width'     => '140px',
        ));

        $this->addColumn('days', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Days in Wishlist'),
            'index'     => 'days_in_wishlist',
            'type'      => 'number',
            'width'     => '140px',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get headers visibility
     *
     * @return bool
     */
    public function getHeadersVisibility()
    {
        return ($this->getCollection()->getSize() > 0);
    }

    /**
     * Get row url
     *
     * @param Mage_Wishlist_Model_Item $item
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }
}

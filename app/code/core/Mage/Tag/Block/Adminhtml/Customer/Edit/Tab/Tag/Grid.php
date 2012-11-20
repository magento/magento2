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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer's tags grid
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @method Mage_Customer_Model_Customer|int getCustomerId() getCustomerId()
 * @method Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag_Grid setCustomerId() setCustomerId(int $customerId)
 * @method Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag_Grid setUseAjax() setUseAjax(boolean $useAjax)
 */
class Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag_Grid extends Mage_Backend_Block_Widget_Grid_Extended
{
    /**
     * Initialize grid parameters
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('tag_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
    }

    /**
     * Prepare data collection for output
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    protected function _prepareCollection()
    {
        if ($this->getCustomerId() instanceof Mage_Customer_Model_Customer) {
            $this->setCustomerId($this->getCustomerId()->getId());
        }

        /** @var $collection Mage_Tag_Model_Resource_Customer_Collection */
        $collection = Mage::getResourceModel('Mage_Tag_Model_Resource_Customer_Collection');
        $collection->addCustomerFilter($this->getCustomerId())
            ->addGroupByTag();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Manual adding of product name
     *
     * @return Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag_Grid
     */
    protected function _afterLoadCollection()
    {
        /** @var $collection Mage_Tag_Model_Resource_Customer_Collection */
        $collection = $this->getCollection();
        $collection->addProductName();
        return parent::_afterLoadCollection();
    }

    /**
     * Add grid columns
     *
     * @return Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header' => Mage::helper('Mage_Tag_Helper_Data')->__('Tag Name'),
            'index'  => 'name',
        ));

        $this->addColumn('status', array(
            'header'  => Mage::helper('Mage_Tag_Helper_Data')->__('Status'),
            'width'   => '90px',
            'index'   => 'status',
            'type'    => 'options',
            'options' => array(
                Mage_Tag_Model_Tag::STATUS_DISABLED => Mage::helper('Mage_Tag_Helper_Data')->__('Disabled'),
                Mage_Tag_Model_Tag::STATUS_PENDING  => Mage::helper('Mage_Tag_Helper_Data')->__('Pending'),
                Mage_Tag_Model_Tag::STATUS_APPROVED => Mage::helper('Mage_Tag_Helper_Data')->__('Approved'),
            ),
            'filter'  => false,
        ));

        $this->addColumn('product', array(
            'header'   => Mage::helper('Mage_Tag_Helper_Data')->__('Product Name'),
            'index'    => 'product',
            'filter'   => false,
            'sortable' => false,
        ));

        $this->addColumn('product_sku', array(
            'header'   => Mage::helper('Mage_Tag_Helper_Data')->__('SKU'),
            'index'    => 'product_sku',
            'filter'   => false,
            'sortable' => false,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Returns URL for editing of row tag
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/tag/edit', array(
            'tag_id'      => $row->getTagId(),
            'customer_id' => $this->getCustomerId(),
        ));
    }

    /**
     * Returns URL for grid updating
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/customer/tagGrid', array(
            '_current' => true,
            'id'       => $this->getCustomerId()
        ));
    }

}

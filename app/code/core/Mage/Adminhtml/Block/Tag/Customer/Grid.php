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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Child Of Mage_Adminhtml_Block_Tag_Customer
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Tag_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        if (Mage::registry('current_tag')) {
            $this->setId('tag_customer_grid' . Mage::registry('current_tag')->getId());
        }
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }
    /*
     * Retrieves Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/customer', array('_current' => true));
    }

    protected function _prepareCollection()
    {
        $tagId = Mage::registry('current_tag')->getId();
        $storeId = Mage::registry('current_tag')->getStoreId();
        $collection = Mage::getModel('Mage_Tag_Model_Tag')
            ->getCustomerCollection()
            ->addTagFilter($tagId)
            ->setCountAttribute('tr.tag_relation_id')
            ->addStoreFilter($storeId)
            ->addGroupByCustomerProduct();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->addProductName();
        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', array(
            'header'        => Mage::helper('Mage_Tag_Helper_Data')->__('ID'),
            'width'         => 50,
            'align'         => 'right',
            'index'         => 'entity_id',
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('First Name'),
            'index'     => 'firstname',
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Last Name'),
            'index'     => 'lastname',
        ));

        $this->addColumn('product', array(
            'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Product Name'),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'product',
        ));

        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Product SKU'),
            'filter'    => false,
            'sortable'  => false,
            'width'     => 50,
            'align'     => 'right',
            'index'     => 'product_sku',
        ));



        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/customer/edit', array('id' => $row->getId()));
    }

}

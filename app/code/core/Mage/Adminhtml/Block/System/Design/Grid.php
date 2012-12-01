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
 * Design changes grid
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Design_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('designGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid data collection
     *
     * @return Mage_Adminhtml_Block_System_Design_Grid
     */
    protected function _prepareCollection()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);

        $collection = Mage::getResourceModel('Mage_Core_Model_Resource_Design_Collection');

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Define grid columns
     *
     * @return Mage_Adminhtml_Block_System_Design_Grid
     */
    protected function _prepareColumns()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'        => Mage::helper('Mage_Catalog_Helper_Data')->__('Store'),
                'width'         => '100px',
                'type'          => 'store',
                'store_view'    => true,
                'sortable'      => false,
                'index'         => 'store_id',
            ));
        }

        $this->addColumn('package', array(
                'header'    => Mage::helper('Mage_Catalog_Helper_Data')->__('Design'),
                'width'     => '150px',
                'index'     => 'design',
        ));
        $this->addColumn('date_from', array(
            'header'    => Mage::helper('Mage_CatalogRule_Helper_Data')->__('Date From'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'date',
            'index'     => 'date_from',
        ));

        $this->addColumn('date_to', array(
            'header'    => Mage::helper('Mage_CatalogRule_Helper_Data')->__('Date To'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'date',
            'index'     => 'date_to',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare row click url
     *
     * @param Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Prepare grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

}

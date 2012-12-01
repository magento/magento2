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
 * Custom Variable Grid Container
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_System_Variable_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Internal constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customVariablesGrid');
        $this->setDefaultSort('variable_id');
        $this->setDefaultDir('ASC');
    }

    /**
     * Prepare grid collection object
     *
     * @return Mage_Adminhtml_Block_System_Variable_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Core_Model_Resource_Variable_Collection */
        $collection = Mage::getModel('Mage_Core_Model_Variable')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Adminhtml_Block_System_Variable_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('variable_id', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Variable ID'),
            'width'     => '1',
            'index'     => 'variable_id',
        ));

        $this->addColumn('code', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Variable Code'),
            'index'     => 'code',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Name'),
            'index'     => 'name',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('variable_id' => $row->getId()));
    }
}

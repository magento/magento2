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
 * Adminhtml customers groups grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Group_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerGroupGrid');
        $this->setDefaultSort('type');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Init customer groups collection
     * @return void
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_Customer_Model_Resource_Group_Collection')
            ->addTaxClass();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Configuration of grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('time', array(
            'header' => Mage::helper('Mage_Customer_Helper_Data')->__('ID'),
            'width' => '50px',
            'align' => 'right',
            'index' => 'customer_group_id',
        ));

        $this->addColumn('type', array(
            'header' => Mage::helper('Mage_Customer_Helper_Data')->__('Group Name'),
            'index' => 'customer_group_code',
        ));

        $this->addColumn('class_name', array(
            'header' => Mage::helper('Mage_Customer_Helper_Data')->__('Tax Class'),
            'index' => 'class_name',
            'width' => '200px'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }

}

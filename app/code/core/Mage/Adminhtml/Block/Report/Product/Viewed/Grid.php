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
 * Adminhtml most viewed products report grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Report_Product_Viewed_Grid extends Mage_Adminhtml_Block_Report_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('gridViewedProducts');
    }

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->initReport('Mage_Reports_Model_Resource_Product_Viewed_Collection');
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Product Name'),
            'index'     =>'name',
            'total'     =>Mage::helper('Mage_Reports_Helper_Data')->__('Subtotal')
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('Mage_Reports_Helper_Data')->__('Price'),
            'width'         => '120px',
            'type'          => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index'         => 'price',
        ));

        $this->addColumn('views', array(
            'header'    =>Mage::helper('Mage_Reports_Helper_Data')->__('Number of Views'),
            'width'     =>'120px',
            'align'     =>'right',
            'index'     =>'views',
            'total'     =>'sum'
        ));

        $this->addExportType('*/*/exportViewedCsv', Mage::helper('Mage_Reports_Helper_Data')->__('CSV'));
        $this->addExportType('*/*/exportViewedExcel', Mage::helper('Mage_Reports_Helper_Data')->__('Excel XML'));

        return parent::_prepareColumns();
    }

}

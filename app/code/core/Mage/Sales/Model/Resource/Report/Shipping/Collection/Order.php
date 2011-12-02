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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales report shipping collection
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Report_Shipping_Collection_Order
    extends Mage_Sales_Model_Resource_Report_Collection_Abstract
{
    /**
     * Period format
     *
     * @var string
     */
    protected $_periodFormat;

    /**
     * Selected columns
     *
     * @var array
     */
    protected $_selectedColumns    = array();

    /**
     * Initialize custom resource model
     *
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('Mage_Adminhtml_Model_Report_Item');
        $this->_resource = Mage::getResourceModel('Mage_Sales_Model_Resource_Report')
            ->init('sales_shipping_aggregated_order');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    /**
     * Get selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $adapter = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
             $this->_periodFormat = $adapter->getDateExtractSql('period', Varien_Db_Adapter_Interface::INTERVAL_YEAR);
        } else {
            $this->_periodFormat = $adapter->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                'shipping_description'  => 'shipping_description',
                'orders_count'          => 'SUM(orders_count)',
                'total_shipping'        => 'SUM(total_shipping)',
                'total_shipping_actual' => 'SUM(total_shipping_actual)',
            );
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return Mage_Sales_Model_Resource_Report_Shipping_Collection_Order
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            $this->getResource()->getMainTable() ,
            $this->_getSelectedColumns()
        );

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat,
                'shipping_description'
            ));
        }
        if ($this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat
            ));
        }
        return $this;
    }
}

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
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Tax report collection
 *
 * @category    Mage
 * @package     Mage_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tax_Model_Resource_Report_Collection extends Mage_Sales_Model_Resource_Report_Collection_Abstract
{
    /**
     * Enter description here ...
     *
     * @var unknown
     */
    protected $_periodFormat;

    /**
     * Aggregated Data Table
     *
     * @var string
     */
    protected $_aggregationTable = 'tax_order_aggregated_created';

    /**
     * Enter description here ...
     *
     * @var unknown
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
        $this->_resource = Mage::getResourceModel('Mage_Sales_Model_Resource_Report')->init($this->_aggregationTable);
        $this->setConnection($this->getResource()->getReadConnection());
    }

    /**
     * Enter description here ...
     *
     * @return unknown
     */
    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y');
        } else {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                'code'                  => 'code',
                'percent'               => 'percent',
                'orders_count'          => 'SUM(orders_count)',
                'tax_base_amount_sum'   => 'SUM(tax_base_amount_sum)'
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
     * @return Mage_Tax_Model_Resource_Report_Collection
     */
    protected function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable() , $this->_getSelectedColumns());
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()->group(array($this->_periodFormat, 'code', 'percent'));
        }

        if ($this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat
            ));
        }

        /**
         * Allow to use analytic function
         */
        $this->_useAnalyticFunction = true;

        return $this;
    }
}

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
 * Sales Collection
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Resource_Sale_Collection extends Varien_Data_Collection_Db
{

    /**
     * Totals data
     *
     * @var array
     */
    protected $_totals = array(
        'lifetime' => 0, 'base_lifetime' => 0, 'base_avgsale' => 0, 'num_orders' => 0);


    /**
     * Customer model
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * Order state value
     *
     * @var null|string|array
     */
    protected $_state = null;

    /**
     * Order state condition
     *
     * @var string
     */
    protected $_orderStateCondition = null;

    /**
     * Set sales order entity and establish read connection
     *
     */
    public function __construct()
    {
        $conn = Mage::getResourceSingleton('Mage_Sales_Model_Resource_Order')->getReadConnection();
        $this->setConnection($conn);
    }

    /**
     * Set filter by customer
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_Sales_Model_Resource_Sale_Collection
     */
    public function setCustomerFilter(Mage_Customer_Model_Customer $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Add filter by stores
     *
     * @param array $storeIds
     * @return Mage_Sales_Model_Resource_Sale_Collection
     */
    public function addStoreFilter($storeIds)
    {
        return $this->addFieldToFilter('store_id', array('in' => $storeIds));
    }

    /**
     * Set filter by order state
     *
     * @param string|array $state
     * @param bool_type $exclude
     * @return Mage_Sales_Model_Resource_Sale_Collection
     */
    public function setOrderStateFilter($state, $exclude = false)
    {
        $this->_orderStateCondition = ($exclude) ? 'NOT IN' : 'IN';
        $this->_orderStateValue     = (!is_array($state)) ? array($state) : $state;
        return $this;
    }
    
    
    /**
     * Before load action
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _beforeLoad()
    {
        $this->getSelect()
            ->from(
                array('sales' => Mage::getResourceSingleton('Mage_Sales_Model_Resource_Order')->getMainTable()),
                array(
                    'store_id',
                    'lifetime'      => new Zend_Db_Expr('SUM(sales.base_grand_total)'),
                    'base_lifetime' => new Zend_Db_Expr('SUM(sales.base_grand_total * sales.base_to_global_rate)'),
                    'avgsale'       => new Zend_Db_Expr('AVG(sales.base_grand_total)'),
                    'base_avgsale'  => new Zend_Db_Expr('AVG(sales.base_grand_total * sales.base_to_global_rate)'),
                    'num_orders'    => new Zend_Db_Expr('COUNT(sales.base_grand_total)')
                )
            )
            ->group('sales.store_id');

        if ($this->_customer instanceof Mage_Customer_Model_Customer) {
            $this->addFieldToFilter('sales.customer_id', $this->_customer->getId());
        }

        if (!is_null($this->_orderStateValue)) {
            $condition = '';
            switch ($this->_orderStateCondition) {
                case 'IN' : 
                    $condition = 'in';
                    break;
                case 'NOT IN' : 
                    $condition = 'nin';
                    break;
            }
            $this->addFieldToFilter('state', array($condition => $this->_orderStateValue));
        }

        Mage::dispatchEvent('sales_sale_collection_query_before', array('collection' => $this));
        return $this;
    }

    /**
     * Load data
     *
     * @return  Varien_Data_Collection_Db
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_beforeLoad();

        $this->_renderFilters()
             ->_renderOrders()
             ->_renderLimit();

        $this->printLogQuery($printQuery, $logQuery);

        $data = $this->getData();
        $this->resetData();

        $stores = Mage::getResourceModel('Mage_Core_Model_Resource_Store_Collection')
            ->setWithoutDefaultFilter()
            ->load()
            ->toOptionHash();
        $this->_items = array();
        foreach ($data as $v) {
            $storeObject = new Varien_Object($v);
            $storeId     = $v['store_id'];
            $storeName   = isset($stores[$storeId]) ? $stores[$storeId] : null;
            $storeObject->setStoreName($storeName)
                ->setWebsiteId(Mage::app()->getStore($storeId)->getWebsiteId())
                ->setAvgNormalized($v['avgsale'] * $v['num_orders']);
            $this->_items[$storeId] = $storeObject;
            foreach ($this->_totals as $key => $value) {
                $this->_totals[$key] += $storeObject->getData($key);
            }
        }
        
        if ($this->_totals['num_orders']) {
            $this->_totals['avgsale'] = $this->_totals['base_lifetime'] / $this->_totals['num_orders'];
        }

        $this->_setIsLoaded();
        $this->_afterLoad();
        return $this;
    }

    /**
     * Retrieve totals data converted into Varien_Object
     *
     * @return Varien_Object
     */
    public function getTotals()
    {
        return new Varien_Object($this->_totals);
    }
}

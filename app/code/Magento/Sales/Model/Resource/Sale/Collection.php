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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Collection
 */
namespace Magento\Sales\Model\Resource\Sale;

class Collection extends \Magento\Data\Collection\Db
{
    /**
     * Totals data
     *
     * @var array
     */
    protected $_totals = array(
        'lifetime' => 0,
        'base_lifetime' => 0,
        'base_avgsale' => 0,
        'num_orders' => 0
    );

    /**
     * Customer model
     *
     * @var \Magento\Customer\Model\Customer
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
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Sales\Model\Resource\Order
     */
    protected $_orderResource;

    /**
     * @var \Magento\Core\Model\Resource\Store\CollectionFactory
     */
    protected $_storeCollFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Set sales order entity and establish read connection
     *
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Sales\Model\Resource\Order $resource
     * @param \Magento\Core\Model\Resource\Store\CollectionFactory $storeCollFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Sales\Model\Resource\Order $resource,
        \Magento\Core\Model\Resource\Store\CollectionFactory $storeCollFactory,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager
    ) {
        $this->_eventManager = $eventManager;
        $this->_orderResource = $resource;
        $this->_storeCollFactory = $storeCollFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($logger, $fetchStrategy, $entityFactory, $this->_orderResource->getReadConnection());
    }

    /**
     * Set filter by customer
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @return \Magento\Sales\Model\Resource\Sale\Collection
     */
    public function setCustomerFilter(\Magento\Customer\Model\Customer $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Add filter by stores
     *
     * @param array $storeIds
     * @return \Magento\Sales\Model\Resource\Sale\Collection
     */
    public function addStoreFilter($storeIds)
    {
        return $this->addFieldToFilter('store_id', array('in' => $storeIds));
    }

    /**
     * Set filter by order state
     *
     * @param string|array $state
     * @param bool $exclude
     * @return \Magento\Sales\Model\Resource\Sale\Collection
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
     * @return \Magento\Data\Collection\Db
     */
    protected function _beforeLoad()
    {
        $this->getSelect()
            ->from(
                array('sales' => $this->_orderResource->getMainTable()),
                array(
                    'store_id',
                    'lifetime'      => new \Zend_Db_Expr('SUM(sales.base_grand_total)'),
                    'base_lifetime' => new \Zend_Db_Expr('SUM(sales.base_grand_total * sales.base_to_global_rate)'),
                    'avgsale'       => new \Zend_Db_Expr('AVG(sales.base_grand_total)'),
                    'base_avgsale'  => new \Zend_Db_Expr('AVG(sales.base_grand_total * sales.base_to_global_rate)'),
                    'num_orders'    => new \Zend_Db_Expr('COUNT(sales.base_grand_total)')
                )
            )
            ->group('sales.store_id');

        if ($this->_customer instanceof \Magento\Customer\Model\Customer) {
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

        $this->_eventManager->dispatch('sales_sale_collection_query_before', array('collection' => $this));
        return $this;
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return  \Magento\Data\Collection\Db
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

        $stores = $this->_storeCollFactory->create()
            ->setWithoutDefaultFilter()
            ->load()
            ->toOptionHash();
        $this->_items = array();
        foreach ($data as $v) {
            $storeObject = new \Magento\Object($v);
            $storeId     = $v['store_id'];
            $storeName   = isset($stores[$storeId]) ? $stores[$storeId] : null;
            $storeObject->setStoreName($storeName)
                ->setWebsiteId($this->_storeManager->getStore($storeId)->getWebsiteId())
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
     * Retrieve totals data converted into \Magento\Object
     *
     * @return \Magento\Object
     */
    public function getTotals()
    {
        return new \Magento\Object($this->_totals);
    }
}

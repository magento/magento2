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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource\Sale;

use Magento\Core\Model\EntityFactory;
use Magento\Framework\StoreManagerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Logger;
use Magento\Sales\Model\Resource\Order;

/**
 * Sales Collection
 */
class Collection extends \Magento\Framework\Data\Collection\Db
{
    /**
     * Totals data
     *
     * @var array
     */
    protected $_totals = array('lifetime' => 0, 'base_lifetime' => 0, 'base_avgsale' => 0, 'num_orders' => 0);

    /**
     * Customer Id
     *
     * @var int
     */
    protected $_customerId;

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
     * @var ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var Order
     */
    protected $_orderResource;

    /**
     * @var \Magento\Store\Model\Resource\Store\CollectionFactory
     */
    protected $_storeCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Order $resource
     * @param \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Order $resource,
        \Magento\Store\Model\Resource\Store\CollectionFactory $storeCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_eventManager = $eventManager;
        $this->_orderResource = $resource;
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $this->_orderResource->getReadConnection());
    }

    /**
     * Set filter by customer Id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerIdFilter($customerId)
    {
        $this->_customerId = (int)$customerId;
        return $this;
    }

    /**
     * Add filter by stores
     *
     * @param array $storeIds
     * @return $this
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
     * @return $this
     */
    public function setOrderStateFilter($state, $exclude = false)
    {
        $this->_orderStateCondition = $exclude ? 'NOT IN' : 'IN';
        $this->_state = !is_array($state) ? array($state) : $state;
        return $this;
    }

    /**
     * Before load action
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->getSelect()->from(
            array('sales' => $this->_orderResource->getMainTable()),
            array(
                'store_id',
                'lifetime' => new \Zend_Db_Expr('SUM(sales.base_grand_total)'),
                'base_lifetime' => new \Zend_Db_Expr('SUM(sales.base_grand_total * sales.base_to_global_rate)'),
                'avgsale' => new \Zend_Db_Expr('AVG(sales.base_grand_total)'),
                'base_avgsale' => new \Zend_Db_Expr('AVG(sales.base_grand_total * sales.base_to_global_rate)'),
                'num_orders' => new \Zend_Db_Expr('COUNT(sales.base_grand_total)')
            )
        )->group(
            'sales.store_id'
        );

        if ($this->_customerId) {
            $this->addFieldToFilter('sales.customer_id', $this->_customerId);
        }

        if (!is_null($this->_state)) {
            $condition = '';
            switch ($this->_orderStateCondition) {
                case 'IN':
                    $condition = 'in';
                    break;
                case 'NOT IN':
                    $condition = 'nin';
                    break;
            }
            $this->addFieldToFilter('state', array($condition => $this->_state));
        }

        $this->_eventManager->dispatch('sales_sale_collection_query_before', array('collection' => $this));
        return $this;
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->_beforeLoad();

        $this->_renderFilters()->_renderOrders()->_renderLimit();

        $this->printLogQuery($printQuery, $logQuery);

        $data = $this->getData();
        $this->resetData();

        $stores = $this->_storeCollectionFactory->create()->setWithoutDefaultFilter()->load()->toOptionHash();
        $this->_items = array();
        foreach ($data as $v) {
            $storeObject = new \Magento\Framework\Object($v);
            $storeId = $v['store_id'];
            $storeName = isset($stores[$storeId]) ? $stores[$storeId] : null;
            $storeObject->setStoreName(
                $storeName
            )->setWebsiteId(
                $this->_storeManager->getStore($storeId)->getWebsiteId()
            )->setAvgNormalized(
                $v['avgsale'] * $v['num_orders']
            );
            $this->_items[$storeId] = $storeObject;
            foreach (array_keys($this->_totals) as $key) {
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
     * Retrieve totals data converted into \Magento\Framework\Object
     *
     * @return \Magento\Framework\Object
     */
    public function getTotals()
    {
        return new \Magento\Framework\Object($this->_totals);
    }
}

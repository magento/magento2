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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Newsletter subscribers collection
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_Model_Resource_Subscriber_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Queue link table name
     *
     * @var string
     */
    protected $_queueLinkTable;

    /**
     * Store table name
     *
     * @var string
     */
    protected $_storeTable;

    /**
     * Queue joined flag
     *
     * @var boolean
     */
    protected $_queueJoinedFlag    = false;

    /**
     * Flag that indicates apply of customers info on load
     *
     * @var boolean
     */
    protected $_showCustomersInfo  = false;

    /**
     * Filter for count
     *
     * @var array
     */
    protected $_countFilterPart    = array();

    /**
     * Constructor
     * Configures collection
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Newsletter_Model_Subscriber', 'Mage_Newsletter_Model_Resource_Subscriber');
        $this->_queueLinkTable = $this->getTable('newsletter_queue_link');
        $this->_storeTable = $this->getTable('core_store');


        // defining mapping for fields represented in several tables
        $this->_map['fields']['customer_lastname'] = 'customer_lastname_table.value';
        $this->_map['fields']['customer_firstname'] = 'customer_firstname_table.value';
        $this->_map['fields']['type'] = $this->getResource()->getReadConnection()
            ->getCheckSql('main_table.customer_id = 0', 1, 2);
        $this->_map['fields']['website_id'] = 'store.website_id';
        $this->_map['fields']['group_id'] = 'store.group_id';
        $this->_map['fields']['store_id'] = 'main_table.store_id';
    }

    /**
     * Set loading mode subscribers by queue
     *
     * @param Mage_Newsletter_Model_Queue $queue
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function useQueue(Mage_Newsletter_Model_Queue $queue)
    {
        $this->getSelect()
            ->join(array('link'=>$this->_queueLinkTable), "link.subscriber_id = main_table.subscriber_id", array())
            ->where("link.queue_id = ? ", $queue->getId());
        $this->_queueJoinedFlag = true;
        return $this;
    }

    /**
     * Set using of links to only unsendet letter subscribers.
     *
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function useOnlyUnsent()
    {
        if ($this->_queueJoinedFlag) {
            $this->addFieldToFilter('link.letter_sent_at', array('null' => 1));
        }

        return $this;
    }

    /**
     * Adds customer info to select
     *
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function showCustomerInfo()
    {
        $adapter = $this->getConnection();
        $customer = Mage::getModel('Mage_Customer_Model_Customer');
        $firstname  = $customer->getAttribute('firstname');
        $lastname   = $customer->getAttribute('lastname');

        $this->getSelect()
            ->joinLeft(
                array('customer_lastname_table'=>$lastname->getBackend()->getTable()),
                $adapter->quoteInto('customer_lastname_table.entity_id=main_table.customer_id
                 AND customer_lastname_table.attribute_id = ?', (int)$lastname->getAttributeId()),
                array('customer_lastname'=>'value')
            )
            ->joinLeft(
                array('customer_firstname_table'=>$firstname->getBackend()->getTable()),
                $adapter->quoteInto('customer_firstname_table.entity_id=main_table.customer_id
                 AND customer_firstname_table.attribute_id = ?', (int)$firstname->getAttributeId()),
                array('customer_firstname'=>'value')
            );

        return $this;
    }

    /**
     * Add type field expression to select
     *
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function addSubscriberTypeField()
    {
        $this->getSelect()
            ->columns(array('type'=>new Zend_Db_Expr($this->_getMappedField('type'))));
        return $this;
    }

    /**
     * Sets flag for customer info loading on load
     *
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function showStoreInfo()
    {
        $this->getSelect()->join(
            array('store' => $this->_storeTable),
            'store.store_id = main_table.store_id',
            array('group_id', 'website_id')
        );

        return $this;
    }

    /**
     * Returns select count sql
     *
     * @return string
     */
    public function getSelectCountSql()
    {

        $select = parent::getSelectCountSql();
        $countSelect = clone $this->getSelect();

        $countSelect->reset(Zend_Db_Select::HAVING);

        return $select;
    }

    /**
     * Load only subscribed customers
     *
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function useOnlyCustomers()
    {
        $this->addFieldToFilter('main_table.customer_id', array('gt' => 0));

        return $this;
    }

    /**
     * Show only with subscribed status
     *
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function useOnlySubscribed()
    {
        $this->addFieldToFilter('main_table.subscriber_status', Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);

        return $this;
    }

    /**
     * Filter collection by specified store ids
     *
     * @param array|int $storeIds
     * @return Mage_Newsletter_Model_Resource_Subscriber_Collection
     */
    public function addStoreFilter($storeIds)
    {
        $this->addFieldToFilter('main_table.store_id', array('in'=>$storeIds));
        return $this;
    }
}

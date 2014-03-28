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
 * @package     Magento_Newsletter
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Newsletter\Model\Resource\Problem;

/**
 * Newsletter problems collection
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Collection extends \Magento\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * True when subscribers info joined
     *
     * @var bool
     */
    protected $_subscribersInfoJoinedFlag = false;

    /**
     * True when grouped
     *
     * @var bool
     */
    protected $_problemGrouped = false;

    /**
     * Customer collection factory
     *
     * @var \Magento\Customer\Model\Resource\Customer\CollectionFactory
     */
    protected $_customerCollectionFactory;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\Resource\Customer\CollectionFactory $customerCollectionFactory
     * @param null|\Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\Resource\Customer\CollectionFactory $customerCollectionFactory,
        $connection = null,
        \Magento\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_customerCollectionFactory = $customerCollectionFactory;
    }

    /**
     * Define resource model and model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Newsletter\Model\Problem', 'Magento\Newsletter\Model\Resource\Problem');
    }

    /**
     * Adds subscribers info
     *
     * @return $this
     */
    public function addSubscriberInfo()
    {
        $this->getSelect()->joinLeft(
            array('subscriber' => $this->getTable('newsletter_subscriber')),
            'main_table.subscriber_id = subscriber.subscriber_id',
            array('subscriber_email', 'customer_id', 'subscriber_status')
        );
        $this->addFilterToMap('subscriber_id', 'main_table.subscriber_id');
        $this->_subscribersInfoJoinedFlag = true;

        return $this;
    }

    /**
     * Adds queue info
     *
     * @return $this
     */
    public function addQueueInfo()
    {
        $this->getSelect()->joinLeft(
            array('queue' => $this->getTable('newsletter_queue')),
            'main_table.queue_id = queue.queue_id',
            array('queue_start_at', 'queue_finish_at')
        )->joinLeft(
            array('template' => $this->getTable('newsletter_template')),
            'queue.template_id = template.template_id',
            array('template_subject', 'template_code', 'template_sender_name', 'template_sender_email')
        );
        return $this;
    }

    /**
     * Loads customers info to collection
     *
     * @return void
     */
    protected function _addCustomersData()
    {
        $customersIds = array();

        foreach ($this->getItems() as $item) {
            if ($item->getCustomerId()) {
                $customersIds[] = $item->getCustomerId();
            }
        }

        if (count($customersIds) == 0) {
            return;
        }

        /** @var \Magento\Customer\Model\Resource\Customer\Collection $customers */
        $customers = $this->_customerCollectionFactory->create();
        $customers->addNameToSelect()->addAttributeToFilter('entity_id', array("in" => $customersIds));

        $customers->load();

        foreach ($customers->getItems() as $customer) {
            $problems = $this->getItemsByColumnValue('customer_id', $customer->getId());
            foreach ($problems as $problem) {
                $problem->setCustomerName(
                    $customer->getName()
                )->setCustomerFirstName(
                    $customer->getFirstName()
                )->setCustomerLastName(
                    $customer->getLastName()
                );
            }
        }
    }

    /**
     * Loads collecion and adds customers info
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        parent::load($printQuery, $logQuery);
        if ($this->_subscribersInfoJoinedFlag && !$this->isLoaded()) {
            $this->_addCustomersData();
        }
        return $this;
    }
}

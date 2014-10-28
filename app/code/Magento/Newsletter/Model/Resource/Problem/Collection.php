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
namespace Magento\Newsletter\Model\Resource\Problem;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Newsletter problems collection
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
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
     * Customer Service
     *
     * @var CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * Customer View Helper
     *
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerView;

    /**
     * checks if customer data is loaded
     *
     * @var boolean
     */
    protected $_loadCustomersDataFlag = false;


    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param CustomerAccountServiceInterface $customerAccountService,
     * @param \Magento\Customer\Helper\View $customerView
     * @param null|\Zend_Db_Adapter_Abstract $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Customer\Helper\View $customerView,
        $connection = null,
        \Magento\Framework\Model\Resource\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_customerAccountService = $customerAccountService;
        $this->_customerView = $customerView;
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
     * Set customer loaded status
     *
     * @param bool $flag
     * @return $this
     */
    protected function _setIsLoaded($flag = true)
    {
        if (!$flag) {
            $this->_loadCustomersDataFlag = false;
        }
        return parent::_setIsLoaded($flag);
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
        if ($this->_loadCustomersDataFlag) {
            return;
        }
        $this->_loadCustomersDataFlag = true;
        foreach ($this->getItems() as $item) {
            if ($item->getCustomerId()) {
                $customerId = $item->getCustomerId();
                try {
                    $customer = $this->_customerAccountService->getCustomer($customerId);
                    $problems = $this->getItemsByColumnValue('customer_id', $customerId);
                    $customerName = $this->_customerView->getCustomerName($customer);
                    foreach ($problems as $problem) {
                        $problem->setCustomerName($customerName)
                            ->setCustomerFirstName($customer->getFirstName())
                            ->setCustomerLastName($customer->getLastName());
                    }
                } catch (NoSuchEntityException $e) {
                    // do nothing if customer is not found by id
                }
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
        if ($this->_subscribersInfoJoinedFlag) {
            $this->_addCustomersData();
        }
        return $this;
    }
}

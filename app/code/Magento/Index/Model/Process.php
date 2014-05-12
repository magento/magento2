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
namespace Magento\Index\Model;

use Magento\Index\Model\Resource\Event\Collection;

/**
 * @method \Magento\Index\Model\Resource\Process _getResource()
 * @method \Magento\Index\Model\Resource\Process getResource()
 * @method string getIndexerCode()
 * @method \Magento\Index\Model\Process setIndexerCode(string $value)
 * @method string getStatus()
 * @method \Magento\Index\Model\Process setStatus(string $value)
 * @method string getStartedAt()
 * @method \Magento\Index\Model\Process setStartedAt(string $value)
 * @method string getEndedAt()
 * @method \Magento\Index\Model\Process setEndedAt(string $value)
 * @method string getMode()
 * @method \Magento\Index\Model\Process setMode(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Process extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Process statuses
     */
    const STATUS_RUNNING = 'working';

    const STATUS_PENDING = 'pending';

    const STATUS_REQUIRE_REINDEX = 'require_reindex';

    /**
     * Process event statuses
     */
    const EVENT_STATUS_NEW = 'new';

    const EVENT_STATUS_DONE = 'done';

    const EVENT_STATUS_ERROR = 'error';

    const EVENT_STATUS_WORKING = 'working';

    /**
     * Process modes
     * Process mode allow disable automatic process events processing
     */
    const MODE_MANUAL = 'manual';

    const MODE_REAL_TIME = 'real_time';

    /**
     * Indexer stategy object
     *
     * @var \Magento\Index\Model\Indexer\AbstractIndexer
     */
    protected $_currentIndexer;

    /**
     * Lock file entity storage
     *
     * @var \Magento\Index\Model\Lock\Storage
     */
    protected $_lockStorage;

    /**
     * Instance of current process file
     *
     * @var \Magento\Index\Model\Process\File
     */
    protected $_processFile;

    /**
     * Event repostiory
     *
     * @var \Magento\Index\Model\EventRepository
     */
    protected $_eventRepository;

    /**
     * @var \Magento\Index\Model\IndexerFactory
     */
    protected $_indexerFactory;

    /**
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexer;

    /**
     * @var \Magento\Index\Model\Resource\Event
     */
    protected $_resourceEvent;

    /**
     * @var \Magento\Index\Model\Indexer\ConfigInterface
     */
    protected $_indexerConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Index\Model\Resource\Event $resourceEvent
     * @param \Magento\Index\Model\Indexer\Factory $indexerFactory
     * @param \Magento\Index\Model\Indexer $indexer
     * @param \Magento\Index\Model\Indexer\ConfigInterface $indexerConfig
     * @param \Magento\Index\Model\Lock\Storage $lockStorage
     * @param \Magento\Index\Model\EventRepository $eventRepository
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Index\Model\Resource\Event $resourceEvent,
        \Magento\Index\Model\Indexer\Factory $indexerFactory,
        \Magento\Index\Model\Indexer $indexer,
        \Magento\Index\Model\Indexer\ConfigInterface $indexerConfig,
        \Magento\Index\Model\Lock\Storage $lockStorage,
        \Magento\Index\Model\EventRepository $eventRepository,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_indexerConfig = $indexerConfig;
        $this->_indexerFactory = $indexerFactory;
        $this->_indexer = $indexer;
        $this->_resourceEvent = $resourceEvent;
        $this->_lockStorage = $lockStorage;
        $this->_eventRepository = $eventRepository;
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Index\Model\Resource\Process');
    }

    /**
     * Set indexer class name as data namespace for event object
     *
     * @param   Event $event
     * @return  $this
     */
    protected function _setEventNamespace(Event $event)
    {
        $namespace = get_class($this->getIndexer());
        $event->setDataNamespace($namespace);
        $event->setProcess($this);
        return $this;
    }

    /**
     * Remove indexer namespace from event
     *
     * @param Event $event
     * @return $this
     */
    protected function _resetEventNamespace($event)
    {
        $event->setDataNamespace(null);
        $event->setProcess(null);
        return $this;
    }

    /**
     * Register data required by process in event object
     *
     * @param Event $event
     * @return $this
     */
    public function register(Event $event)
    {
        if ($this->matchEvent($event)) {
            $this->_setEventNamespace($event);
            $this->getIndexer()->register($event);
            $event->addProcessId($this->getId());
            $this->_resetEventNamespace($event);
            if ($this->getMode() == self::MODE_MANUAL) {
                $this->_getResource()->updateStatus($this, self::STATUS_REQUIRE_REINDEX);
            }
        }
        return $this;
    }

    /**
     * Check if event can be matched by process
     *
     * @param Event $event
     * @return bool
     */
    public function matchEvent(Event $event)
    {
        return $this->getIndexer()->matchEvent($event);
    }

    /**
     * Check if specific entity and action type is matched
     *
     * @param   string $entity
     * @param   string $type
     * @return  bool
     */
    public function matchEntityAndType($entity, $type)
    {
        if ($entity !== null && $type !== null) {
            return $this->getIndexer()->matchEntityAndType($entity, $type);
        }
        return true;
    }

    /**
     * Reindex all data what this process responsible is
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     * @throws \Exception
     */
    public function reindexAll()
    {
        if ($this->isLocked()) {
            throw new \Magento\Framework\Model\Exception(
                __(
                    '%1 Index process is not working now. Please try running this process later.',
                    $this->getIndexer()->getName()
                )
            );
        }

        $processStatus = $this->getStatus();

        $this->_getResource()->startProcess($this);
        $this->lock();
        try {
            $eventsCollection = $this->_eventRepository->getUnprocessed($this);
            if ($processStatus == self::STATUS_PENDING && $eventsCollection->getSize() > 0 ||
                $this->getForcePartialReindex()
            ) {
                $this->_getResource()->beginTransaction();
                try {
                    $this->_processEventsCollection($eventsCollection, false);
                    $this->_getResource()->commit();
                } catch (\Exception $e) {
                    $this->_getResource()->rollBack();
                    throw $e;
                }
            } else {
                //Update existing events since we'll do reindexAll
                $this->_resourceEvent->updateProcessEvents($this);
                $this->getIndexer()->reindexAll();
            }
            $this->unlock();

            if ($this->getMode() == self::MODE_MANUAL && $this->_eventRepository->hasUnprocessed($this)) {
                $this->_getResource()->updateStatus($this, self::STATUS_REQUIRE_REINDEX);
            } else {
                $this->_getResource()->endProcess($this);
            }
        } catch (\Exception $e) {
            $this->unlock();
            $this->_getResource()->failProcess($this);
            throw $e;
        }
        $this->_eventManager->dispatch('after_reindex_process_' . $this->getIndexerCode());
    }

    /**
     * Reindex all data what this process responsible is
     * Check and using depends processes
     *
     * @return $this|void
     */
    public function reindexEverything()
    {
        if ($this->getData('runed_reindexall')) {
            return $this;
        }

        $this->setForcePartialReindex(
            $this->getStatus() == self::STATUS_PENDING && $this->_eventRepository->hasUnprocessed($this)
        );

        if ($this->getDepends()) {
            foreach ($this->getDepends() as $code) {
                $process = $this->_indexer->getProcessByCode($code);
                if ($process) {
                    $process->reindexEverything();
                }
            }
        }

        $this->setData('runed_reindexall', true);
        return $this->reindexAll();
    }

    /**
     * Process event with assigned indexer object
     *
     * @param Event $event
     * @return $this
     */
    public function processEvent(Event $event)
    {
        if (!$this->matchEvent($event)) {
            return $this;
        }
        if ($this->getMode() == self::MODE_MANUAL) {
            $this->changeStatus(self::STATUS_REQUIRE_REINDEX);
            return $this;
        }

        // Commented  due to deadlock
        // @todo: Verify: It is required for partial update
        //$this->_getResource()->updateProcessStartDate($this);

        $this->_setEventNamespace($event);
        $isError = false;

        try {
            $this->getIndexer()->processEvent($event);
        } catch (\Exception $e) {
            $isError = true;
        }
        $event->resetData();
        $this->_resetEventNamespace($event);
        //$this->_getResource()->updateProcessEndDate($this);
        $event->addProcessId($this->getId(), $isError ? self::EVENT_STATUS_ERROR : self::EVENT_STATUS_DONE);

        return $this;
    }

    /**
     * Get Indexer strategy object
     *
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Index\Model\IndexerInterface
     */
    public function getIndexer()
    {
        if ($this->_currentIndexer === null) {
            $name = $this->_getData('indexer_code');
            if (!$name) {
                throw new \Magento\Framework\Model\Exception(__('Indexer name is not defined.'));
            }
            $indexerConfiguration = $this->_indexerConfig->getIndexer($name);
            if (!$indexerConfiguration || empty($indexerConfiguration['instance'])) {
                throw new \Magento\Framework\Model\Exception(__('Indexer model is not defined.'));
            }
            $indexerModel = $this->_indexerFactory->create($indexerConfiguration['instance']);
            if ($indexerModel instanceof \Magento\Index\Model\Indexer\AbstractIndexer) {
                $this->_currentIndexer = $indexerModel;
            } else {
                throw new \Magento\Framework\Model\Exception(
                    __('Indexer model should extend \Magento\Index\Model\Indexer\Abstract.')
                );
            }
        }
        return $this->_currentIndexer;
    }

    /**
     * Index pending events addressed to the process
     *
     * @param   null|string $entity
     * @param   null|string $type
     * @return  $this
     * @throws \Exception
     */
    public function indexEvents($entity = null, $type = null)
    {
        /**
         * Check if process indexer can match entity code and action type
         */
        if ($entity !== null && $type !== null) {
            if (!$this->getIndexer()->matchEntityAndType($entity, $type)) {
                return $this;
            }
        }

        if ($this->getMode() == self::MODE_MANUAL) {
            return $this;
        }

        if ($this->isLocked()) {
            return $this;
        }

        $this->lock();
        try {
            /**
             * Prepare events collection
             */
            $eventsCollection = $this->_eventRepository->getUnprocessed($this);
            if ($entity !== null) {
                $eventsCollection->addEntityFilter($entity);
            }
            if ($type !== null) {
                $eventsCollection->addTypeFilter($type);
            }

            $this->_processEventsCollection($eventsCollection);
            $this->unlock();
        } catch (\Exception $e) {
            $this->unlock();
            throw $e;
        }
        return $this;
    }

    /**
     * Process all events of the collection
     *
     * @param Collection $eventsCollection
     * @param bool $skipUnmatched
     * @return $this
     */
    protected function _processEventsCollection(Collection $eventsCollection, $skipUnmatched = true)
    {
        // We can't reload the collection because of transaction
        /** @var $event \Magento\Index\Model\Event */
        while (true == ($event = $eventsCollection->fetchItem())) {
            try {
                $this->processEvent($event);
                if (!$skipUnmatched) {
                    $eventProcessIds = $event->getProcessIds();
                    if (!isset($eventProcessIds[$this->getId()])) {
                        $event->addProcessId($this->getId(), null);
                    }
                }
            } catch (\Exception $e) {
                $event->addProcessId($this->getId(), self::EVENT_STATUS_ERROR);
            }
            $event->save();
        }
        return $this;
    }

    /**
     * Update status process/event association
     *
     * @param   Event $event
     * @param   string $status
     * @return  $this
     */
    public function updateEventStatus(Event $event, $status)
    {
        $this->_getResource()->updateEventStatus($this->getId(), $event->getId(), $status);
        return $this;
    }

    /**
     * Get process file instance
     *
     * @return \Magento\Index\Model\Process\File
     */
    protected function _getProcessFile()
    {
        if (!$this->_processFile) {
            $this->_processFile = $this->_lockStorage->getFile($this->getId());
        }
        return $this->_processFile;
    }

    /**
     * Lock process without blocking.
     * This method allow protect multiple process running and fast lock validation.
     *
     * @return $this
     */
    public function lock()
    {
        $this->_getProcessFile()->processLock();
        return $this;
    }

    /**
     * Lock and block process.
     * If new instance of the process will try validate locking state
     * script will wait until process will be unlocked
     *
     * @return $this
     */
    public function lockAndBlock()
    {
        $this->_getProcessFile()->processLock(false);
        return $this;
    }

    /**
     * Unlock process
     *
     * @return $this
     */
    public function unlock()
    {
        $this->_getProcessFile()->processUnlock();
        return $this;
    }

    /**
     * Check if process is locked by another user
     *
     * @param bool $needUnlock
     * @return bool
     */
    public function isLocked($needUnlock = true)
    {
        return $this->_getProcessFile()->isProcessLocked($needUnlock);
    }

    /**
     * Change process status
     *
     * @param string $status
     * @return $this
     */
    public function changeStatus($status)
    {
        $this->_eventManager->dispatch('index_process_change_status', array('process' => $this, 'status' => $status));
        $this->_getResource()->updateStatus($this, $status);
        return $this;
    }

    /**
     * Get list of process mode options
     *
     * @return array
     */
    public function getModesOptions()
    {
        return array(self::MODE_REAL_TIME => __('Update on Save'), self::MODE_MANUAL => __('Manual Update'));
    }

    /**
     * Get list of process status options
     *
     * @return array
     */
    public function getStatusesOptions()
    {
        return array(
            self::STATUS_PENDING => __('Ready'),
            self::STATUS_RUNNING => __('Processing'),
            self::STATUS_REQUIRE_REINDEX => __('Reindex Required')
        );
    }

    /**
     * Get list of "Update Required" options
     *
     * @return array
     */
    public function getUpdateRequiredOptions()
    {
        return array(0 => __('No'), 1 => __('Yes'));
    }

    /**
     * Retrieve depend indexer codes
     *
     * @return array
     */
    public function getDepends()
    {
        $depends = $this->getData('depends');
        if (is_null($depends)) {
            $depends = array();
            $indexerConfiguration = $this->_indexerConfig->getIndexer($this->getIndexerCode());
            if ($indexerConfiguration) {
                if (isset($indexerConfiguration['depends']) && is_array($indexerConfiguration['depends'])) {
                    $depends = $indexerConfiguration['depends'];
                }
            }

            $this->setData('depends', $depends);
        }

        return $depends;
    }

    /**
     * Process event with locks checking
     *
     * @param Event $event
     * @return $this
     * @throws \Exception
     */
    public function safeProcessEvent(Event $event)
    {
        if (!$this->matchEvent($event)) {
            return $this;
        }
        if ($this->isLocked()) {
            return $this;
        }
        $this->lock();
        try {
            $this->processEvent($event);
            $this->unlock();
        } catch (\Exception $e) {
            $this->unlock();
            throw $e;
        }
        return $this;
    }
}

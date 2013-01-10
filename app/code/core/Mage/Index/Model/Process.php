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
 * @package     Mage_Index
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 *
 * @method Mage_Index_Model_Resource_Process _getResource()
 * @method Mage_Index_Model_Resource_Process getResource()
 * @method string getIndexerCode()
 * @method Mage_Index_Model_Process setIndexerCode(string $value)
 * @method string getStatus()
 * @method Mage_Index_Model_Process setStatus(string $value)
 * @method string getStartedAt()
 * @method Mage_Index_Model_Process setStartedAt(string $value)
 * @method string getEndedAt()
 * @method Mage_Index_Model_Process setEndedAt(string $value)
 * @method string getMode()
 * @method Mage_Index_Model_Process setMode(string $value)
 *
 * @category    Mage
 * @package     Mage_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Index_Model_Process extends Mage_Core_Model_Abstract
{
    const XML_PATH_INDEXER_DATA     = 'global/index/indexer';
    /**
     * Process statuses
     */
    const STATUS_RUNNING            = 'working';
    const STATUS_PENDING            = 'pending';
    const STATUS_REQUIRE_REINDEX    = 'require_reindex';

    /**
     * Process event statuses
     */
    const EVENT_STATUS_NEW          = 'new';
    const EVENT_STATUS_DONE         = 'done';
    const EVENT_STATUS_ERROR        = 'error';
    const EVENT_STATUS_WORKING      = 'working';

    /**
     * Process modes
     * Process mode allow disable automatic process events processing
     */
    const MODE_MANUAL              = 'manual';
    const MODE_REAL_TIME           = 'real_time';

    /**
     * Indexer stategy object
     *
     * @var Mage_Index_Model_Indexer_Abstract
     */
    protected $_indexer = null;

    /**
     * Lock file entity storage
     *
     * @var Mage_Index_Model_Lock_Storage
     */
    protected $_lockStorage;

    /**
     * Instance of current process file
     *
     * @var Mage_Index_Model_Process_File
     */
    protected $_processFile;

    /**
     * @param Mage_Core_Model_Event_Manager $eventDispatcher
     * @param Mage_Core_Model_Cache $cacheManager
     * @param Mage_Core_Model_Resource_Abstract $resource
     * @param Varien_Data_Collection_Db $resourceCollection
     * @param Mage_Index_Model_Lock_Storage $lockStorage
     * @param array $data
     */
    public function __construct(
        Mage_Core_Model_Event_Manager $eventDispatcher,
        Mage_Core_Model_Cache $cacheManager,
        Mage_Index_Model_Lock_Storage $lockStorage,
        Mage_Core_Model_Resource_Abstract $resource = null,
        Varien_Data_Collection_Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($eventDispatcher, $cacheManager, $resource, $resourceCollection, $data);
        $this->_lockStorage = $lockStorage;
    }

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('Mage_Index_Model_Resource_Process');
    }

    /**
     * Set indexer class name as data namespace for event object
     *
     * @param   Mage_Index_Model_Event $event
     * @return  Mage_Index_Model_Process
     */
    protected function _setEventNamespace(Mage_Index_Model_Event $event)
    {
        $namespace = get_class($this->getIndexer());
        $event->setDataNamespace($namespace);
        $event->setProcess($this);
        return $this;
    }

    /**
     * Remove indexer namespace from event
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Index_Model_Process
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
     * @param Mage_Index_Model_Event $event
     * @return Mage_Index_Model_Process
     */
    public function register(Mage_Index_Model_Event $event)
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
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
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
     */
    public function reindexAll()
    {
        if ($this->isLocked()) {
            Mage::throwException(Mage::helper('Mage_Index_Helper_Data')->__('%s Index process is working now. Please try run this process later.', $this->getIndexer()->getName()));
        }

        $processStatus = $this->getStatus();

        $this->_getResource()->startProcess($this);
        $this->lock();
        try {
            $eventsCollection = $this->getUnprocessedEventsCollection();

            /** @var $eventResource Mage_Index_Model_Resource_Event */
            $eventResource = Mage::getResourceSingleton('Mage_Index_Model_Resource_Event');

            if ($eventsCollection->count() > 0 && $processStatus == self::STATUS_PENDING
                || $this->getForcePartialReindex()
            ) {
                $this->_getResource()->beginTransaction();
                try {
                    $this->_processEventsCollection($eventsCollection, false);
                    $this->_getResource()->commit();
                } catch (Exception $e) {
                    $this->_getResource()->rollBack();
                    throw $e;
                }
            } else {
                //Update existing events since we'll do reindexAll
                $eventResource->updateProcessEvents($this);
                $this->getIndexer()->reindexAll();
            }
            $this->unlock();

            $unprocessedEvents = $eventResource->getUnprocessedEvents($this);
            if ($this->getMode() == self::MODE_MANUAL && (count($unprocessedEvents) > 0)) {
                $this->_getResource()->updateStatus($this, self::STATUS_REQUIRE_REINDEX);
            } else {
                $this->_getResource()->endProcess($this);
            }
        } catch (Exception $e) {
            $this->unlock();
            $this->_getResource()->failProcess($this);
            throw $e;
        }
        Mage::dispatchEvent('after_reindex_process_' . $this->getIndexerCode());
    }

    /**
     * Reindex all data what this process responsible is
     * Check and using depends processes
     *
     * @return Mage_Index_Model_Process
     */
    public function reindexEverything()
    {
        if ($this->getData('runed_reindexall')) {
            return $this;
        }

        /** @var $eventResource Mage_Index_Model_Resource_Event */
        $eventResource = Mage::getResourceSingleton('Mage_Index_Model_Resource_Event');
        $unprocessedEvents = $eventResource->getUnprocessedEvents($this);
        $this->setForcePartialReindex(count($unprocessedEvents) > 0 && $this->getStatus() == self::STATUS_PENDING);

        if ($this->getDepends()) {
            /** @var $indexer Mage_Index_Model_Indexer */
            $indexer = Mage::getSingleton('Mage_Index_Model_Indexer');
            foreach ($this->getDepends() as $code) {
                $process = $indexer->getProcessByCode($code);
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
     * @param Mage_Index_Model_Event $event
     * @return Mage_Index_Model_Process
     */
    public function processEvent(Mage_Index_Model_Event $event)
    {
        if (!$this->matchEvent($event)) {
            return $this;
        }
        if ($this->getMode() == self::MODE_MANUAL) {
            $this->changeStatus(self::STATUS_REQUIRE_REINDEX);
            return $this;
        }

        $this->_getResource()->updateProcessStartDate($this);
        $this->_setEventNamespace($event);
        $isError = false;

        try {
            $this->getIndexer()->processEvent($event);
        } catch (Exception $e) {
            $isError = true;
        }
        $event->resetData();
        $this->_resetEventNamespace($event);
        $this->_getResource()->updateProcessEndDate($this);
        $event->addProcessId($this->getId(), $isError ? self::EVENT_STATUS_ERROR : self::EVENT_STATUS_DONE);

        return $this;
    }

    /**
     * Get Indexer strategy object
     *
     * @return Mage_Index_Model_Indexer_Abstract
     */
    public function getIndexer()
    {
        if ($this->_indexer === null) {
            $code = $this->_getData('indexer_code');
            if (!$code) {
                Mage::throwException(Mage::helper('Mage_Index_Helper_Data')->__('Indexer code is not defined.'));
            }
            $xmlPath = self::XML_PATH_INDEXER_DATA . '/' . $code;
            $config = Mage::getConfig()->getNode($xmlPath);
            if (!$config || empty($config->model)) {
                Mage::throwException(Mage::helper('Mage_Index_Helper_Data')->__('Indexer model is not defined.'));
            }
            $model = Mage::getModel((string)$config->model);
            if ($model instanceof Mage_Index_Model_Indexer_Abstract) {
                $this->_indexer = $model;
            } else {
                Mage::throwException(Mage::helper('Mage_Index_Helper_Data')->__('Indexer model should extend Mage_Index_Model_Indexer_Abstract.'));
            }
        }
        return $this->_indexer;
    }

    /**
     * Index pending events addressed to the process
     *
     * @param   null|string $entity
     * @param   null|string $type
     * @return  Mage_Index_Model_Process
     */
    public function indexEvents($entity=null, $type=null)
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
            $eventsCollection = $this->getUnprocessedEventsCollection();
            if ($entity !== null) {
                $eventsCollection->addEntityFilter($entity);
            }
            if ($type !== null) {
                $eventsCollection->addTypeFilter($type);
            }

            $this->_processEventsCollection($eventsCollection);
            $this->unlock();
        } catch (Exception $e) {
            $this->unlock();
            throw $e;
        }
        return $this;
    }

    /**
     * Process all events of the collection
     *
     * @param Mage_Index_Model_Resource_Event_Collection $eventsCollection
     * @param bool $skipUnmatched
     * @return Mage_Index_Model_Process
     */
    protected function _processEventsCollection(
        Mage_Index_Model_Resource_Event_Collection $eventsCollection,
        $skipUnmatched = true
    ) {
        // We can't reload the collection because of transaction
        /** @var $event Mage_Index_Model_Event */
        while ($event = $eventsCollection->fetchItem()) {
            try {
                $this->processEvent($event);
                if (!$skipUnmatched) {
                    $eventProcessIds = $event->getProcessIds();
                    if (!isset($eventProcessIds[$this->getId()])) {
                        $event->addProcessId($this->getId(), null);
                    }
                }
            } catch (Exception $e) {
                $event->addProcessId($this->getId(), self::EVENT_STATUS_ERROR);
            }
            $event->save();
        }
        return $this;
    }

    /**
     * Update status process/event association
     *
     * @param   Mage_Index_Model_Event $event
     * @param   string $status
     * @return  Mage_Index_Model_Process
     */
    public function updateEventStatus(Mage_Index_Model_Event $event, $status)
    {
        $this->_getResource()->updateEventStatus($this->getId(), $event->getId(), $status);
        return $this;
    }

    /**
     * Get process file instance
     *
     * @return Mage_Index_Model_Process_File
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
     * @return Mage_Index_Model_Process
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
     * @return Mage_Index_Model_Process
     */
    public function lockAndBlock()
    {
        $this->_getProcessFile()->processLock(false);
        return $this;
    }

    /**
     * Unlock process
     *
     * @return Mage_Index_Model_Process
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
    public function isLocked($needUnlock = false)
    {
        return $this->_getProcessFile()->isProcessLocked($needUnlock);
    }

    /**
     * Change process status
     *
     * @param string $status
     * @return Mage_Index_Model_Process
     */
    public function changeStatus($status)
    {
        Mage::dispatchEvent('index_process_change_status', array(
            'process' => $this,
            'status' => $status
        ));
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
        return array(
            self::MODE_REAL_TIME => Mage::helper('Mage_Index_Helper_Data')->__('Update on Save'),
            self::MODE_MANUAL => Mage::helper('Mage_Index_Helper_Data')->__('Manual Update')
        );
    }

    /**
     * Get list of process status options
     *
     * @return array
     */
    public function getStatusesOptions()
    {
        return array(
            self::STATUS_PENDING            => Mage::helper('Mage_Index_Helper_Data')->__('Ready'),
            self::STATUS_RUNNING            => Mage::helper('Mage_Index_Helper_Data')->__('Processing'),
            self::STATUS_REQUIRE_REINDEX    => Mage::helper('Mage_Index_Helper_Data')->__('Reindex Required'),
        );
    }

    /**
     * Get list of "Update Required" options
     *
     * @return array
     */
    public function getUpdateRequiredOptions()
    {
        return array(
            0 => Mage::helper('Mage_Index_Helper_Data')->__('No'),
            1 => Mage::helper('Mage_Index_Helper_Data')->__('Yes'),
        );
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
            $path = self::XML_PATH_INDEXER_DATA . '/' . $this->getIndexerCode();
            $node = Mage::getConfig()->getNode($path);
            if ($node) {
                $data = $node->asArray();
                if (isset($data['depends']) && is_array($data['depends'])) {
                    $depends = array_keys($data['depends']);
                }
            }

            $this->setData('depends', $depends);
        }

        return $depends;
    }

    /**
     * Process event with locks checking
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Index_Model_Process
     * @throws Exception
     */
    public function safeProcessEvent(Mage_Index_Model_Event $event)
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
        } catch (Exception $e) {
            $this->unlock();
            throw $e;
        }
        return $this;
    }

    /**
     * Get unprocessed events collection
     *
     * @return Mage_Index_Model_Resource_Event_Collection
     */
    public function getUnprocessedEventsCollection()
    {
        /** @var $eventsCollection Mage_Index_Model_Resource_Event_Collection */
        $eventsCollection = Mage::getResourceModel('Mage_Index_Model_Resource_Event_Collection');
        $eventsCollection->addProcessFilter($this, self::EVENT_STATUS_NEW);
        return $eventsCollection;
    }
}

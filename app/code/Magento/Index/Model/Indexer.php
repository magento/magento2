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
 * @package     Magento_Index
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Indexer strategy
 */
namespace Magento\Index\Model;

class Indexer
{
    /**
     * Collection of available processes
     *
     * @var \Magento\Index\Model\Resource\Process\Collection
     */
    protected $_processesCollection;

    /**
     * @var \Magento\Index\Model\Resource\Process
     */
    protected $_resourceProcess;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Index\Model\EventFactory
     */
    protected $_indexEventFactory;

    /**
     * @var \Magento\Index\Model\Resource\Process\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Index\Model\Resource\Process\CollectionFactory $collectionFactory
     * @param \Magento\Index\Model\Resource\Process $resourceProcess
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Index\Model\EventFactory $indexEventFactory
     */
    public function __construct(
        \Magento\Index\Model\Resource\Process\CollectionFactory $collectionFactory,
        \Magento\Index\Model\Resource\Process $resourceProcess,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Index\Model\EventFactory $indexEventFactory
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceProcess = $resourceProcess;
        $this->_eventManager = $eventManager;
        $this->_indexEventFactory = $indexEventFactory;
        $this->_processesCollection = $this->_createCollection();
    }

    /**
     * @return \Magento\Index\Model\Resource\Process\Collection
     */
    private function _createCollection()
    {
        return $this->_collectionFactory->create();
    }

    /**
     * Get collection of all available processes
     *
     * @return \Magento\Index\Model\Resource\Process\Collection
     */
    public function getProcessesCollection()
    {
        return $this->_processesCollection;
    }


    /**
     * Get index process by specific id
     *
     * @param int $processId
     * @return \Magento\Index\Model\Process | false
     */
    public function getProcessById($processId)
    {
        foreach ($this->_processesCollection as $process) {
            if ($process->getId() == $processId) {
                return $process;
            }
        }
        return false;
    }

    /**
     * Get index process by specific code
     *
     * @param string $code
     * @return \Magento\Index\Model\Process | false
     */
    public function getProcessByCode($code)
    {
        foreach ($this->_processesCollection as $process) {
            if ($process->getIndexerCode() == $code) {
                return $process;
            }
        }
        return false;
    }

    /**
     * Indexing all pending events.
     * Events set can be limited by event entity and type
     *
     * @param   null | string $entity
     * @param   null | string $type
     * @return  \Magento\Index\Model\Indexer
     * @throws Exception
     */
    public function indexEvents($entity=null, $type=null)
    {
        $this->_eventManager->dispatch('start_index_events' . $this->_getEventTypeName($entity, $type));
        $this->_resourceProcess->beginTransaction();
        try {
            $this->_runAll('indexEvents', array($entity, $type));
            $this->_resourceProcess->commit();
        } catch (\Exception $e) {
            $this->_resourceProcess->rollBack();
            throw $e;
        }
        $this->_eventManager->dispatch('end_index_events' . $this->_getEventTypeName($entity, $type));
        return $this;
    }

    /**
     * Index one event by all processes
     *
     * @param   \Magento\Index\Model\Event $event
     * @return  \Magento\Index\Model\Indexer
     */
    public function indexEvent(\Magento\Index\Model\Event $event)
    {
        $this->_runAll('safeProcessEvent', array($event));
        return $this;
    }

    /**
     * Register event in each indexing process process
     *
     * @param \Magento\Index\Model\Event $event
     * @return $this
     */
    public function registerEvent(\Magento\Index\Model\Event $event)
    {
        $this->_runAll('register', array($event));
        return $this;
    }

    /**
     * Create new event log and register event in all processes
     *
     * @param   \Magento\Object $entity
     * @param   string $entityType
     * @param   string $eventType
     * @param   bool $doSave
     * @return  \Magento\Index\Model\Event
     */
    public function logEvent(\Magento\Object $entity, $entityType, $eventType, $doSave=true)
    {
        $event = $this->_indexEventFactory->create()
            ->setEntity($entityType)
            ->setType($eventType)
            ->setDataObject($entity)
            ->setEntityPk($entity->getId());

        $this->registerEvent($event);
        if ($doSave) {
            $event->save();
        }
        return $event;
    }

    /**
     * Create new event log and register event in all processes.
     * Initiate events indexing procedure.
     *
     * @param   \Magento\Object $entity
     * @param   string $entityType
     * @param   string $eventType
     * @return  \Magento\Index\Model\Indexer
     * @throws Exception
     */
    public function processEntityAction(\Magento\Object $entity, $entityType, $eventType)
    {
        $event = $this->logEvent($entity, $entityType, $eventType, false);
        /**
         * Index and save event just in case if some process matched it
         */
        if ($event->getProcessIds()) {
            $this->_eventManager->dispatch('start_process_event' . $this->_getEventTypeName($entityType, $eventType));
            $this->_resourceProcess->beginTransaction();
            try {
                $this->indexEvent($event);
                $this->_resourceProcess->commit();
            } catch (\Exception $e) {
                $this->_resourceProcess->rollBack();
                throw $e;
            }
            $event->save();
            $this->_eventManager->dispatch('end_process_event' . $this->_getEventTypeName($entityType, $eventType));
        }
        return $this;
    }

    /**
     * Reindex all processes
     */
    public function reindexAll()
    {
        $this->_reindexCollection($this->_createCollection());
    }

    /**
     * Reindex only processes that are invalidated
     */
    public function reindexRequired()
    {
        $collection = $this->_createCollection();
        $collection->addFieldToFilter('status', \Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX);
        $this->_reindexCollection($collection);
    }

    /**
     * Sub-routine for iterating collection and reindexing all processes of specified collection
     *
     * @param \Magento\Index\Model\Resource\Process\Collection $collection
     */
    private function _reindexCollection(\Magento\Index\Model\Resource\Process\Collection $collection)
    {
        /** @var $process \Magento\Index\Model\Process */
        foreach ($collection as $process) {
            $process->reindexEverything();
        }
    }

    /**
     * Run all processes method with parameters
     * Run by depends priority
     * Not recursive call is not implement
     *
     * @param string $method
     * @param array $args
     * @return \Magento\Index\Model\Indexer
     */
    protected function _runAll($method, $args)
    {
        $checkLocks = $method != 'register';
        $processed = array();
        foreach ($this->_processesCollection as $process) {
            $code = $process->getIndexerCode();
            if (in_array($code, $processed)) {
                continue;
            }
            $hasLocks = false;

            if ($process->getDepends()) {
                foreach ($process->getDepends() as $processCode) {
                    $dependProcess = $this->getProcessByCode($processCode);
                    if ($dependProcess && !in_array($processCode, $processed)) {
                        if ($checkLocks && $dependProcess->isLocked()) {
                            $hasLocks = true;
                        } else {
                            call_user_func_array(array($dependProcess, $method), $args);
                            if ($checkLocks && $dependProcess->getMode() == \Magento\Index\Model\Process::MODE_MANUAL) {
                                $hasLocks = true;
                            } else {
                                $processed[] = $processCode;
                            }
                        }
                    }
                }
            }

            if (!$hasLocks) {
                call_user_func_array(array($process, $method), $args);
                $processed[] = $code;
            }
        }
    }

    /**
     * Get event type name
     *
     * @param null|string $entityType
     * @param null|string $eventType
     * @return string
     */
    protected function _getEventTypeName($entityType = null, $eventType = null)
    {
        $eventName = $entityType . '_' . $eventType;
        $eventName = trim($eventName, '_');
        if (!empty($eventName)) {
            $eventName = '_' . $eventName;
        }
        return $eventName;
    }
}

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

use Magento\Index\Model\Indexer;

/**
 * @method \Magento\Index\Model\Resource\Event _getResource()
 * @method \Magento\Index\Model\Resource\Event getResource()
 * @method \Magento\Index\Model\Event setType(string $value)
 * @method \Magento\Index\Model\Event setEntity(string $value)
 * @method int getEntityPk()
 * @method \Magento\Index\Model\Event setEntityPk(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Index\Model\Event setCreatedAt(string $value)
 * @method \Magento\Index\Model\Event setOldData(string $value)
 * @method \Magento\Index\Model\Event setNewData(string $value)
 * @method \Magento\Framework\Object getDataObject()
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Event extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Predefined event types
     */
    const TYPE_SAVE = 'save';

    const TYPE_DELETE = 'delete';

    const TYPE_MASS_ACTION = 'mass_action';

    const TYPE_REINDEX = 'reindex';

    /**
     * Array of related processes ids
     * @var array
     */
    protected $_processIds = null;

    /**
     * New and old data namespace. Used for separate processes data
     *
     * @var string
     */
    protected $_dataNamespace = null;

    /**
     * Process object which currently working with event
     *
     * @var null|Process $process
     */
    protected $_process = null;

    /**
     * @var Indexer
     */
    protected $_indexer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Indexer $indexer
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Indexer $indexer,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_indexer = $indexer;
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Index\Model\Resource\Event');
    }

    /**
     * Specify process object
     *
     * @param null|\Magento\Index\Model\Process $process
     * @return $this
     */
    public function setProcess($process)
    {
        $this->_process = $process;
        return $this;
    }

    /**
     * Get related process object
     *
     * @return Process|null
     */
    public function getProcess()
    {
        return $this->_process;
    }

    /**
     * Specify namespace for old and new data
     *
     * @param string $namespace
     * @return $this
     */
    public function setDataNamespace($namespace)
    {
        $this->_dataNamespace = $namespace;
        return $this;
    }

    /**
     * Reset old and new data arrays
     *
     * @return $this
     */
    public function resetData()
    {
        if ($this->_dataNamespace) {
            $data = $this->getNewData(false);
            $data[$this->_dataNamespace] = null;
            $this->setNewData($data);
        } else {
            $this->setNewData(array());
        }
        return $this;
    }

    /**
     * Add process id to event object
     *
     * @param string|int $processId
     * @param string $status
     * @return  $this
     */
    public function addProcessId($processId, $status = \Magento\Index\Model\Process::EVENT_STATUS_NEW)
    {
        $this->_processIds[$processId] = $status;
        return $this;
    }

    /**
     * Get event process ids
     *
     * @return array
     */
    public function getProcessIds()
    {
        return $this->_processIds;
    }

    /**
     * Merge new data
     *
     * @param array $previous
     * @param mixed $current
     * @return array
     */
    protected function _mergeNewDataRecursive($previous, $current)
    {
        if (!is_array($current)) {
            if (!is_null($current)) {
                $previous[] = $current;
            }
            return $previous;
        }

        foreach ($previous as $key => $value) {
            if (array_key_exists($key, $current) && !is_null($current[$key]) && is_array($previous[$key])) {
                if (!is_string($key) || is_array($current[$key])) {
                    $current[$key] = $this->_mergeNewDataRecursive($previous[$key], $current[$key]);
                }
            } elseif (!array_key_exists($key, $current) || is_null($current[$key])) {
                $current[$key] = $previous[$key];
            } elseif (!is_array($previous[$key]) && !is_string($key)) {
                $current[] = $previous[$key];
            }
        }

        return $current;
    }

    /**
     * Merge previous event data to object.
     * Used for events duplicated protection
     *
     * @param array $data
     * @return $this
     */
    public function mergePreviousData($data)
    {
        if (!empty($data['event_id'])) {
            $this->setId($data['event_id']);
            $this->setCreatedAt($data['created_at']);
        }

        if (!empty($data['new_data'])) {
            $previousNewData = unserialize($data['new_data']);
            $currentNewData = $this->getNewData(false);
            $currentNewData = $this->_mergeNewDataRecursive($previousNewData, $currentNewData);
            $this->setNewData(serialize($currentNewData));
        }
        return $this;
    }

    /**
     * Clean new data, unset data for done processes
     *
     * @return $this
     */
    public function cleanNewData()
    {
        $processIds = $this->getProcessIds();
        if (!is_array($processIds) || empty($processIds)) {
            return $this;
        }

        $newData = $this->getNewData(false);
        foreach ($processIds as $processId => $processStatus) {
            if ($processStatus == \Magento\Index\Model\Process::EVENT_STATUS_DONE) {
                $process = $this->_indexer->getProcessById($processId);
                if ($process) {
                    $namespace = get_class($process->getIndexer());
                    if (array_key_exists($namespace, $newData)) {
                        unset($newData[$namespace]);
                    }
                }
            }
        }
        $this->setNewData(serialize($newData));

        return $this;
    }

    /**
     * Get event old data array
     *
     * @param bool $useNamespace
     * @return array
     *
     * @deprecated since 1.6.2.0
     */
    public function getOldData($useNamespace = true)
    {
        return array();
    }

    /**
     * Get event new data array
     *
     * @param bool $useNamespace
     * @return array
     */
    public function getNewData($useNamespace = true)
    {
        $data = $this->_getData('new_data');
        if (is_string($data)) {
            $data = unserialize($data);
        } elseif (empty($data) || !is_array($data)) {
            $data = array();
        }
        if ($useNamespace && $this->_dataNamespace) {
            return isset($data[$this->_dataNamespace]) ? $data[$this->_dataNamespace] : array();
        }
        return $data;
    }

    /**
     * Add new values to old data array (overwrite if value with same key exist)
     *
     * @param array|string $key
     * @param null|mixed $value
     * @return $this
     *
     * @deprecated since 1.6.2.0
     */
    public function addOldData($key, $value = null)
    {
        return $this;
    }

    /**
     * Add new values to new data array (overwrite if value with same key exist)
     *
     * @param array|string $key
     * @param null|mixed $value
     * @return $this
     */
    public function addNewData($key, $value = null)
    {
        $newData = $this->getNewData(false);
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        if ($this->_dataNamespace) {
            if (!isset($newData[$this->_dataNamespace])) {
                $newData[$this->_dataNamespace] = array();
            }
            $newData[$this->_dataNamespace] = array_merge($newData[$this->_dataNamespace], $key);
        } else {
            $newData = array_merge($newData, $key);
        }
        $this->setNewData($newData);
        return $this;
    }

    /**
     * Get event entity code.
     * Entity code declare what kind of data object related with event (product, category etc.)
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->_getData('entity');
    }

    /**
     * Get event action type.
     * Data related on self::TYPE_* constants
     *
     * @return string
     */
    public function getType()
    {
        return $this->_getData('type');
    }

    /**
     * Serelaize old and new data arrays before saving
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        $newData = $this->getNewData(false);
        $this->setNewData(serialize($newData));
        if (!$this->hasCreatedAt()) {
            $this->setCreatedAt($this->dateTime->formatDate(time(), true));
        }
        return parent::_beforeSave();
    }
}

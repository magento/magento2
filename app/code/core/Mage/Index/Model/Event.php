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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 *
 * @method Mage_Index_Model_Resource_Event _getResource()
 * @method Mage_Index_Model_Resource_Event getResource()
 * @method Mage_Index_Model_Event setType(string $value)
 * @method Mage_Index_Model_Event setEntity(string $value)
 * @method int getEntityPk()
 * @method Mage_Index_Model_Event setEntityPk(int $value)
 * @method string getCreatedAt()
 * @method Mage_Index_Model_Event setCreatedAt(string $value)
 * @method Mage_Index_Model_Event setOldData(string $value)
 * @method Mage_Index_Model_Event setNewData(string $value)
 * @method Varien_Object getDataObject()
 *
 * @category    Mage
 * @package     Mage_Index
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Index_Model_Event extends Mage_Core_Model_Abstract
{
    /**
     * Predefined event types
     */
    const TYPE_SAVE        = 'save';
    const TYPE_DELETE      = 'delete';
    const TYPE_MASS_ACTION = 'mass_action';
    const TYPE_REINDEX     = 'reindex';

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
     */
    protected $_process = null;

    /**
     * Initialize resource
     */
    protected function _construct()
    {
        $this->_init('Mage_Index_Model_Resource_Event');
    }

    /**
     * Specify process object
     *
     * @param null|Mage_Index_Model_Process $process
     */
    public function setProcess($process)
    {
        $this->_process = $process;
        return $this;
    }

    /**
     * Get related process object
     *
     * @return Mage_Index_Model_Process | null
     */
    public function getProcess()
    {
        return $this->_process;
    }

    /**
     * Specify namespace for old and new data
     */
    public function setDataNamespace($namespace)
    {
        $this->_dataNamespace = $namespace;
        return $this;
    }

    /**
     * Reset old and new data arrays
     *
     * @return Mage_Index_Model_Event
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
     * @param   $processId
     * @return  Mage_Index_Model_Event
     */
    public function addProcessId($processId, $status=Mage_Index_Model_Process::EVENT_STATUS_NEW)
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
     * @return Mage_Index_Model_Event
     */
    public function mergePreviousData($data)
    {
        if (!empty($data['event_id'])) {
            $this->setId($data['event_id']);
            $this->setCreatedAt($data['created_at']);
        }

        if (!empty($data['new_data'])) {
            $previousNewData = unserialize($data['new_data']);
            $currentNewData  = $this->getNewData(false);
            $currentNewData = $this->_mergeNewDataRecursive($previousNewData, $currentNewData);
            $this->setNewData(serialize($currentNewData));
        }
        return $this;
    }

    /**
     * Clean new data, unset data for done processes
     *
     * @return Mage_Index_Model_Event
     */
    public function cleanNewData()
    {
        $processIds = $this->getProcessIds();
        if (!is_array($processIds) || empty($processIds)) {
            return $this;
        }

        $newData = $this->getNewData(false);
        foreach ($processIds as $processId => $processStatus) {
            if ($processStatus == Mage_Index_Model_Process::EVENT_STATUS_DONE) {
                $process = Mage::getSingleton('Mage_Index_Model_Indexer')->getProcessById($processId);
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
     * @deprecated since 1.6.2.0
     * @param bool $useNamespace
     * @return array
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
     * @deprecated since 1.6.2.0
     * @param array | string $data
     * @param null | mixed $value
     * @return Mage_Index_Model_Event
     */
    public function addOldData($key, $value=null)
    {
        return $this;
    }

    /**
     * Add new values to new data array (overwrite if value with same key exist)
     *
     * @param array | string $data
     * @param null | mixed $value
     * @return Mage_Index_Model_Event
     */
    public function addNewData($key, $value=null)
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
     * @return Mage_Index_Model_Event
     */
    protected function _beforeSave()
    {
        $newData = $this->getNewData(false);
        $this->setNewData(serialize($newData));
        if (!$this->hasCreatedAt()) {
            $this->setCreatedAt($this->_getResource()->formatDate(time(), true));
        }
        return parent::_beforeSave();
    }
}

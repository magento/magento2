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
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Dataflow Batch model
 *
 * @method Mage_Dataflow_Model_Resource_Batch _getResource()
 * @method Mage_Dataflow_Model_Resource_Batch getResource()
 * @method int getProfileId()
 * @method Mage_Dataflow_Model_Batch setProfileId(int $value)
 * @method int getStoreId()
 * @method Mage_Dataflow_Model_Batch setStoreId(int $value)
 * @method string getAdapter()
 * @method Mage_Dataflow_Model_Batch setAdapter(string $value)
 * @method string getCreatedAt()
 * @method Mage_Dataflow_Model_Batch setCreatedAt(string $value)
 *
 * @category    Mage
 * @package     Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Dataflow_Model_Batch extends Mage_Core_Model_Abstract
{
    /**
     * Lifetime abandoned batches
     *
     */
    const LIFETIME = 86400;

    /**
     * Field list collection array
     *
     * @var array
     */
    protected $_fieldList = array();

    /**
     * Dataflow batch io adapter
     *
     * @var Mage_Dataflow_Model_Batch_Io
     */
    protected $_ioAdapter;

    /**
     * Dataflow batch export model
     *
     * @var Mage_Dataflow_Model_Batch_Export
     */
    protected $_batchExport;

    /**
     * Dataflow batch import model
     *
     * @var Mage_Dataflow_Model_Batch_Import
     */
    protected $_batchImport;

    /**
     * Init model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Dataflow_Model_Resource_Batch');
    }

    /**
     * Retrieve prepared field list
     *
     * @return array
     */
    public function getFieldList()
    {
        return $this->_fieldList;
    }

    /**
     * Parse row fields
     *
     * @param array $row
     */
    public function parseFieldList($row)
    {
        foreach ($row as $fieldName => $value) {
            if (!in_array($fieldName, $this->_fieldList)) {
                $this->_fieldList[$fieldName] = $fieldName;
            }
        }
        unset($fieldName, $value, $row);
    }

    /**
     * Retrieve Io Adapter
     *
     * @return Mage_Dataflow_Model_Batch_Io
     */
    public function getIoAdapter()
    {
        if (is_null($this->_ioAdapter)) {
            $this->_ioAdapter = Mage::getModel('Mage_Dataflow_Model_Batch_Io');
            $this->_ioAdapter->init($this);
        }
        return $this->_ioAdapter;
    }

    protected function _beforeSave()
    {
        if (is_null($this->getData('created_at'))) {
            $this->setData('created_at', Mage::getSingleton('Mage_Core_Model_Date')->gmtDate());
        }
    }

    protected function _afterDelete()
    {
        $this->getIoAdapter()->clear();
    }

    /**
     * Retrieve Batch export model
     *
     * @return Mage_Dataflow_Model_Batch_Export
     */
    public function getBatchExportModel()
    {
        if (is_null($this->_batchExport)) {
            $object = Mage::getModel('Mage_Dataflow_Model_Batch_Export');
            $object->setBatchId($this->getId());
            $this->_batchExport = Varien_Object_Cache::singleton()->save($object);
        }
        return Varien_Object_Cache::singleton()->load($this->_batchExport);
    }

    /**
     * Retrieve Batch import model
     *
     * @return Mage_Dataflow_Model_Import_Export
     */
    public function getBatchImportModel()
    {
        if (is_null($this->_batchImport)) {
            $object = Mage::getModel('Mage_Dataflow_Model_Batch_Import');
            $object->setBatchId($this->getId());
            $this->_batchImport = Varien_Object_Cache::singleton()->save($object);
        }
        return Varien_Object_Cache::singleton()->load($this->_batchImport);
    }

    /**
     * Run finish actions for Adapter
     *
     */
    public function beforeFinish()
    {
        if ($this->getAdapter()) {
            $adapter = Mage::getModel($this->getAdapter());
            if (method_exists($adapter, 'finish')) {
                $adapter->finish();
            }
        }
    }

    /**
     * Set additional params
     * automatic convert to serialize data
     *
     * @param mixed $data
     * @return Mage_Dataflow_Model_Batch_Abstract
     */
    public function setParams($data)
    {
        $this->setData('params', serialize($data));
        return $this;
    }

    /**
     * Retrieve additional params
     * return unserialize data
     *
     * @return mixed
     */
    public function getParams()
    {
        $data = $this->_data['params'];
        $data = unserialize($data);
        return $data;
    }
}

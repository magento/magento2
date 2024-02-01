<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\Phrase;

/**
 * Abstract resource model
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractDb extends AbstractResource
{
    /**
     * Cached resources singleton
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resources;

    /**
     * @var string
     */
    protected $connectionName = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;

    /**
     * @var array
     */
    protected $_connections = [];

    /**
     * @var string
     */
    protected $_resourceModel;

    /**
     * @var array
     */
    protected $_tables = [];

    /**
     * @var string
     */
    protected $_mainTable;

    /**
     * @var string
     */
    protected $_idFieldName;

    /**
     * @var bool
     */
    protected $_isPkAutoIncrement = true;

    /**
     * @var bool
     */
    protected $_useIsObjectNew = false;

    /**
     * @var array
     */
    protected $_mainTableFields;

    /**
     * Main table unique keys field names
     * could array(
     *   array('field' => 'db_field_name1', 'title' => 'Field 1 should be unique')
     *   array('field' => 'db_field_name2', 'title' => 'Field 2 should be unique')
     *   array(
     *      'field' => array('db_field_name3', 'db_field_name3'),
     *      'title' => 'Field 3 and Field 4 combination should be unique'
     *   )
     * )
     * or string 'my_field_name' - will be autoconverted to
     *      array( array( 'field' => 'my_field_name', 'title' => 'my_field_name' ) )
     *
     * @var array|null
     */
    protected $_uniqueFields = null;

    /**
     * Serializable fields declaration
     * Structure: array(
     *     <field_name> => array(
     *         <default_value_for_serialization>,
     *         <default_for_unserialization>,
     *         <whether_to_unset_empty_when serializing> // optional parameter
     *     ),
     * )
     *
     * @var array
     */
    protected $_serializableFields = [];

    /**
     * @var TransactionManagerInterface
     */
    protected $transactionManager;

    /**
     * @var ObjectRelationProcessor
     */
    protected $objectRelationProcessor;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        $this->transactionManager = $context->getTransactionManager();
        $this->_resources = $context->getResources();
        $this->objectRelationProcessor = $context->getObjectRelationProcessor();
        if ($connectionName !== null) {
            $this->connectionName = $connectionName;
        }
        parent::__construct();
    }

    /**
     * Provide variables to serialize
     *
     * @return array
     */
    public function __sleep()
    {
        $properties = array_keys(get_object_vars($this));
        $properties = array_diff($properties, ['_resources', '_connections']);
        return $properties;
    }

    /**
     * Restore global dependencies
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\App\ResourceConnection::class);
    }

    /**
     * Standard resource model initialization
     *
     * @param string $mainTable
     * @param string $idFieldName
     * @return void
     */
    protected function _init($mainTable, $idFieldName)
    {
        $this->_setMainTable($mainTable, $idFieldName);
    }

    /**
     * Initialize connections and tables for this resource model
     * If one or both arguments are string, will be used as prefix
     * If $tables is null and $connections is string, $tables will be the same
     *
     * @param string|array $connections
     * @param string|array|null $tables
     * @return $this
     */
    protected function _setResource($connections, $tables = null)
    {
        if (is_array($connections)) {
            foreach ($connections as $key => $value) {
                $this->_connections[$key] = $this->_resources->getConnection($value);
            }
        } elseif (is_string($connections)) {
            $this->connectionName = $connections;
        }

        if ($tables === null && is_string($connections)) {
            $this->_resourceModel = $this->connectionName;
        } elseif (is_array($tables)) {
            foreach ($tables as $key => $value) {
                $this->_tables[$key] = $this->_resources->getTableName($value);
            }
        } elseif (is_string($tables)) {
            $this->_resourceModel = $tables;
        }
        return $this;
    }

    /**
     * Main table setter.
     *
     * Set main entity table name and primary key field name.
     * If field name is omitted {table_name}_id will be used.
     *
     * @param string $mainTable
     * @param string|null $idFieldName
     * @return $this
     */
    protected function _setMainTable($mainTable, $idFieldName = null)
    {
        $this->_mainTable = $mainTable;
        if (null === $idFieldName) {
            $idFieldName = $mainTable . '_id';
        }

        $this->_idFieldName = $idFieldName;
        return $this;
    }

    /**
     * Get primary key field name
     *
     * @throws LocalizedException
     * @return string
     */
    public function getIdFieldName()
    {
        if (empty($this->_idFieldName)) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Empty identifier field name'));
        }
        return $this->_idFieldName;
    }

    /**
     * Main table getter.
     *
     * Returns main table name - extracted from "module/table" style and
     * validated by db adapter.
     *
     * @throws LocalizedException
     * @return string
     */
    public function getMainTable()
    {
        if (empty($this->_mainTable)) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Empty main table name'));
        }
        return $this->getTable($this->_mainTable);
    }

    /**
     * Get real table name for db table, validated by db adapter
     *
     * @param string $tableName
     * @return string
     */
    public function getTable($tableName)
    {
        if (is_array($tableName)) {
            $cacheName = join('@', $tableName);
            list($tableName, $entitySuffix) = $tableName;
        } else {
            $cacheName = $tableName;
            $entitySuffix = null;
        }

        if ($entitySuffix !== null) {
            $tableName .= '_' . $entitySuffix;
        }

        if (!isset($this->_tables[$cacheName])) {
            $connectionName = $this->connectionName;
            $this->_tables[$cacheName] = $this->_resources->getTableName($tableName, $connectionName);
        }
        return $this->_tables[$cacheName];
    }

    /**
     * Get connection by resource name
     *
     * @param string $resourceName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false
     */
    protected function _getConnection($resourceName)
    {
        if (isset($this->_connections[$resourceName])) {
            return $this->_connections[$resourceName];
        }
        $fullResourceName = ($this->connectionName ? $this->connectionName . '_' : '') . $resourceName;
        $connectionInstance = $this->_resources->getConnection($fullResourceName);
        // cache only active connections to detect inactive ones as soon as they become active
        if ($connectionInstance) {
            $this->_connections[$resourceName] = $connectionInstance;
        }
        return $connectionInstance;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false
     */
    public function getConnection()
    {
        $fullResourceName = ($this->connectionName ? $this->connectionName : ResourceConnection::DEFAULT_CONNECTION);
        return $this->_resources->getConnection($fullResourceName);
    }

    /**
     * Load an object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        $object->beforeLoad($value, $field);
        if ($field === null) {
            $field = $this->getIdFieldName();
        }

        $connection = $this->getConnection();
        if ($connection && $value !== null) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);
        $object->afterLoad();
        $object->setOrigData();
        $object->setHasDataChanges(false);

        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $field = $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
        $select = $this->getConnection()->select()->from($this->getMainTable())->where($field . '=?', $value);
        return $select;
    }

    /**
     * Save object object data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Exception
     * @throws AlreadyExistsException
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        $this->beginTransaction();

        try {
            if (!$this->isModified($object)) {
                $this->processNotModifiedSave($object);
                $this->commit();
                $object->setHasDataChanges(false);
                return $this;
            }
            $object->validateBeforeSave();
            $object->beforeSave();
            if ($object->isSaveAllowed()) {
                $this->_serializeFields($object);
                $this->_beforeSave($object);
                $this->_checkUnique($object);
                $this->objectRelationProcessor->validateDataIntegrity($this->getMainTable(), $object->getData());
                if ($this->isObjectNotNew($object)) {
                    $this->updateObject($object);
                } else {
                    $this->saveNewObject($object);
                }
                $this->unserializeFields($object);
                $this->processAfterSaves($object);
            }
            $this->addCommitCallback([$object, 'afterCommitCallback'])->commit();
            $object->setHasDataChanges(false);
        } catch (DuplicateException $e) {
            $this->rollBack();
            $object->setHasDataChanges(true);
            throw new AlreadyExistsException(new Phrase('Unique constraint violation found'), $e);
        } catch (\Exception $e) {
            $this->rollBack();
            $object->setHasDataChanges(true);
            throw $e;
        }
        return $this;
    }

    /**
     * Delete the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->transactionManager->start($this->getConnection());
        try {
            $object->beforeDelete();
            $this->_beforeDelete($object);
            $this->objectRelationProcessor->delete(
                $this->transactionManager,
                $connection,
                $this->getMainTable(),
                $this->getConnection()->quoteInto($this->getIdFieldName() . '=?', $object->getId()),
                $object->getData()
            );
            $this->_afterDelete($object);
            $object->isDeleted(true);
            $object->afterDelete();
            $this->transactionManager->commit();
            $object->afterDeleteCommit();
        } catch (\Exception $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Add unique field restriction
     *
     * @param array|string $field
     * @return $this
     */
    public function addUniqueField($field)
    {
        if ($this->_uniqueFields === null) {
            $this->_initUniqueFields();
        }
        if (is_array($this->_uniqueFields)) {
            $this->_uniqueFields[] = $field;
        }
        return $this;
    }

    /**
     * Reset unique fields restrictions
     *
     * @return $this
     */
    public function resetUniqueField()
    {
        $this->_uniqueFields = [];
        return $this;
    }

    /**
     * Unserialize serializeable object fields
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function unserializeFields(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($this->_serializableFields as $field => $parameters) {
            list($serializeDefault, $unserializeDefault) = $parameters;
            $this->_unserializeField($object, $field, $unserializeDefault);
        }
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [];
        return $this;
    }

    /**
     * Get configuration of all unique fields
     *
     * @return array
     */
    public function getUniqueFields()
    {
        if ($this->_uniqueFields === null) {
            $this->_initUniqueFields();
        }
        return $this->_uniqueFields;
    }

    /**
     * Prepare data for save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     * @throws LocalizedException
     */
    protected function _prepareDataForSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this->_prepareDataForTable($object, $this->getMainTable());
    }

    /**
     * Check that model data fields that can be saved has really changed comparing with origData.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     * @throws LocalizedException
     */
    public function hasDataChanged($object)
    {
        if (!$object->getOrigData()) {
            return true;
        }

        $fields = $this->getConnection()->describeTable($this->getMainTable());
        foreach (array_keys($fields) as $field) {
            if ($object->getOrigData($field) != $object->getData($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function _prepareValueForSave($value, $type)
    {
        return $this->_prepareTableValueForSave($value, $type);
    }

    /**
     * Check for unique values existence
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws AlreadyExistsException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _checkUnique(\Magento\Framework\Model\AbstractModel $object)
    {
        $existent = [];
        $fields = $this->getUniqueFields();
        if (!empty($fields)) {
            if (!is_array($fields)) {
                $fields = $this->_uniqueFields = [['field' => $fields, 'title' => $fields]];
            }

            $data = new \Magento\Framework\DataObject($this->_prepareDataForSave($object));
            $select = $this->getConnection()->select()->from($this->getMainTable());

            foreach ($fields as $unique) {
                $select->reset(\Magento\Framework\DB\Select::WHERE);
                foreach ((array)$unique['field'] as $field) {
                    $value = $data->getData($field);
                    if ($value === null) {
                        $select->where($field . ' IS NULL');
                    } else {
                        $select->where($field . '=?', trim($value));
                    }
                }

                if ($object->getId() || (string)$object->getId() === '0') {
                    $select->where($this->getIdFieldName() . '!=?', $object->getId());
                }

                $test = $this->getConnection()->fetchRow($select);
                if ($test) {
                    $existent[] = $unique['title'];
                }
            }
        }

        if (!empty($existent)) {
            if (count($existent) == 1) {
                $error = new \Magento\Framework\Phrase('%1 already exists.', [$existent[0]]);
            } else {
                $error = new \Magento\Framework\Phrase('%1 already exist.', [implode(', ', $existent)]);
            }
            throw new AlreadyExistsException($error);
        }
        return $this;
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this;
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this;
    }

    /**
     * Perform actions before object delete
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this;
    }

    /**
     * Perform actions after object delete
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this;
    }

    /**
     * Serialize serializable fields of the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _serializeFields(\Magento\Framework\Model\AbstractModel $object)
    {
        foreach ($this->_serializableFields as $field => $parameters) {
            list($serializeDefault, $unserializeDefault) = $parameters;
            $this->_serializeField($object, $field, $serializeDefault, isset($parameters[2]));
        }
    }

    /**
     * Retrieve table checksum
     *
     * @param string|array $table
     * @return int|array|false
     */
    public function getChecksum($table)
    {
        if (!$this->getConnection()) {
            return false;
        }
        $checksum = $this->getConnection()->getTablesChecksum($table);
        if (count($checksum) == 1) {
            return $checksum[$table];
        }
        return $checksum;
    }

    /**
     * Get the array of data fields that was changed or added
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     * @throws LocalizedException
     */
    protected function prepareDataForUpdate($object)
    {
        $data = $object->getData();
        foreach ($object->getStoredData() as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] === $value) {
                unset($data[$key]);
            }
        }
        $dataObject = clone $object;
        $dataObject->setData($data);
        $data = $this->_prepareDataForTable($dataObject, $this->getMainTable());
        unset($data[$this->getIdFieldName()]);
        unset($dataObject);

        return $data;
    }

    /**
     * Check if object is new
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isObjectNotNew(\Magento\Framework\Model\AbstractModel $object)
    {
        return $object->getId() !== null && (!$this->_useIsObjectNew || !$object->isObjectNew());
    }

    /**
     * Save New Object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @throws LocalizedException
     * @return void
     */
    protected function saveNewObject(\Magento\Framework\Model\AbstractModel $object)
    {
        $bind = $this->_prepareDataForSave($object);
        if ($this->_isPkAutoIncrement) {
            unset($bind[$this->getIdFieldName()]);
        }
        $this->getConnection()->insert($this->getMainTable(), $bind);

        if ($this->_isPkAutoIncrement) {
            $object->setId($this->getConnection()->lastInsertId($this->getMainTable()));
        }

        if ($this->_useIsObjectNew) {
            $object->isObjectNew(false);
        }
    }

    /**
     * Check if column data type is numeric
     *
     * Based on column description
     *
     * @param array $columnDescription
     * @return bool
     */
    private function isNumericValue(array $columnDescription): bool
    {
        $result = true;
        if (!empty($columnDescription['DATA_TYPE'])
            && in_array($columnDescription['DATA_TYPE'], ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'])) {
            $result = false;
        }
        return $result;
    }

    /**
     * Update existing object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @throws LocalizedException
     * @return void
     */
    protected function updateObject(\Magento\Framework\Model\AbstractModel $object)
    {
        $connection = $this->getConnection();
        $tableDescription = $connection->describeTable($this->getMainTable());
        $preparedValue = $connection->prepareColumnValue($tableDescription[$this->getIdFieldName()], $object->getId());
        $condition  = (!$this->isNumericValue($tableDescription[$this->getIdFieldName()]))
            ? sprintf('%s=%d', $this->getIdFieldName(), $preparedValue)
            : $connection->quoteInto($this->getIdFieldName() . '=?', $preparedValue);

        /**
         * Not auto increment primary key support
         */
        if ($this->_isPkAutoIncrement) {
            $data = $this->prepareDataForUpdate($object);
            if (!empty($data)) {
                $connection->update($this->getMainTable(), $data, $condition);
            }
        } else {
            $select = $connection->select()->from(
                $this->getMainTable(),
                [$this->getIdFieldName()]
            )->where(
                $condition
            );
            if ($connection->fetchOne($select) !== false) {
                $data = $this->prepareDataForUpdate($object);
                if (!empty($data)) {
                    $connection->update($this->getMainTable(), $data, $condition);
                }
            } else {
                $connection->insert(
                    $this->getMainTable(),
                    $this->_prepareDataForSave($object)
                );
            }
        }
    }

    /**
     * Sequences of after save call
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    protected function processAfterSaves(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_afterSave($object);
        $object->afterSave();
    }

    /**
     * Check if object was modified
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isModified(\Magento\Framework\Model\AbstractModel $object)
    {
        return $object->hasDataChanges();
    }

    /**
     * Process object which was modified
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function processNotModifiedSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return $this;
    }

    /**
     * Perform actions after entity load
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     */
    public function afterLoad(\Magento\Framework\DataObject $object)
    {
        $this->_afterLoad($object);
    }

    /**
     * Perform actions before entity save
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     * @since 100.1.0
     */
    public function beforeSave(\Magento\Framework\DataObject $object)
    {
        $this->_beforeSave($object);
    }

    /**
     * Perform actions after entity save
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     * @since 100.1.0
     */
    public function afterSave(\Magento\Framework\DataObject $object)
    {
        $this->_afterSave($object);
    }

    /**
     * Perform actions before entity delete
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     * @since 100.1.0
     */
    public function beforeDelete(\Magento\Framework\DataObject $object)
    {
        $this->_beforeDelete($object);
    }

    /**
     * Perform actions after entity delete
     *
     * @param \Magento\Framework\DataObject $object
     * @return void
     * @since 100.1.0
     */
    public function afterDelete(\Magento\Framework\DataObject $object)
    {
        $this->_afterDelete($object);
    }

    /**
     * Serialize serializable fields of the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\AbstractModel|void
     * @since 100.1.0
     */
    public function serializeFields(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->_serializeFields($object);
        return $object;
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Resource;

/**
 * Abstract resource model
 */
abstract class AbstractResource
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_writeAdapter;

    /**
     * Main constructor
     */
    public function __construct()
    {
        /**
         * Please override this one instead of overriding real __construct constructor
         */
        $this->_construct();
    }

    /**
     * Array of callbacks subscribed to commit transaction commit
     *
     * @var array
     */
    protected static $_commitCallbacks = [];

    /**
     * Resource initialization
     *
     * @return void
     */
    abstract protected function _construct();

    /**
     * Retrieve connection for read data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    abstract protected function _getReadAdapter();

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    abstract protected function _getWriteAdapter();

    /**
     * Start resource transaction
     *
     * @return $this
     */
    public function beginTransaction()
    {
        $this->_getWriteAdapter()->beginTransaction();
        return $this;
    }

    /**
     * Subscribe some callback to transaction commit
     *
     * @param array $callback
     * @return $this
     */
    public function addCommitCallback($callback)
    {
        $adapterKey = spl_object_hash($this->_getWriteAdapter());
        self::$_commitCallbacks[$adapterKey][] = $callback;
        return $this;
    }

    /**
     * Commit resource transaction
     *
     * @return $this
     */
    public function commit()
    {
        $this->_getWriteAdapter()->commit();
        /**
         * Process after commit callbacks
         */
        if ($this->_getWriteAdapter()->getTransactionLevel() === 0) {
            $adapterKey = spl_object_hash($this->_getWriteAdapter());
            if (isset(self::$_commitCallbacks[$adapterKey])) {
                $callbacks = self::$_commitCallbacks[$adapterKey];
                self::$_commitCallbacks[$adapterKey] = [];
                foreach ($callbacks as $callback) {
                    call_user_func($callback);
                }
            }
        }
        return $this;
    }

    /**
     * Roll back resource transaction
     *
     * @return $this
     */
    public function rollBack()
    {
        $this->_getWriteAdapter()->rollBack();
        return $this;
    }

    /**
     * Serialize specified field in an object
     *
     * @param \Magento\Framework\Object $object
     * @param string $field
     * @param mixed $defaultValue
     * @param bool $unsetEmpty
     * @return $this
     */
    protected function _serializeField(\Magento\Framework\Object $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        $value = $object->getData($field);
        if (empty($value)) {
            if ($unsetEmpty) {
                $object->unsetData($field);
            } else {
                if (is_object($defaultValue) || is_array($defaultValue)) {
                    $defaultValue = serialize($defaultValue);
                }
                $object->setData($field, $defaultValue);
            }
        } elseif (is_array($value) || is_object($value)) {
            $object->setData($field, serialize($value));
        }

        return $this;
    }

    /**
     * Unserialize \Magento\Framework\Object field in an object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $field
     * @param mixed $defaultValue
     * @return void
     */
    protected function _unserializeField(\Magento\Framework\Object $object, $field, $defaultValue = null)
    {
        $value = $object->getData($field);
        if (empty($value)) {
            $object->setData($field, $defaultValue);
        } elseif (!is_array($value) && !is_object($value)) {
            $object->setData($field, unserialize($value));
        }
    }

    /**
     * Prepare data for passed table
     *
     * @param \Magento\Framework\Object $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(\Magento\Framework\Object $object, $table)
    {
        $data = [];
        $fields = $this->_getWriteAdapter()->describeTable($table);
        foreach (array_keys($fields) as $field) {
            if ($object->hasData($field)) {
                $fieldValue = $object->getData($field);
                if ($fieldValue instanceof \Zend_Db_Expr) {
                    $data[$field] = $fieldValue;
                } else {
                    if (null !== $fieldValue) {
                        $fieldValue = $this->_prepareTableValueForSave($fieldValue, $fields[$field]['DATA_TYPE']);
                        $data[$field] = $this->_getWriteAdapter()->prepareColumnValue($fields[$field], $fieldValue);
                    } elseif (!empty($fields[$field]['NULLABLE'])) {
                        $data[$field] = null;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function _prepareTableValueForSave($value, $type)
    {
        $type = strtolower($type);
        if ($type == 'decimal' || $type == 'numeric' || $type == 'float') {
            $value = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Framework\Locale\FormatInterface'
            )->getNumber(
                $value
            );
        }
        return $value;
    }

    /**
     * Template method to return validate rules to be executed before entity is saved
     *
     * @return null
     */
    public function getValidationRulesBeforeSave()
    {
        return null;
    }

    /**
     * Prepare the list of entity fields that should be selected from DB. Apply filtration based on active fieldset.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $tableName
     * @return array|string
     */
    protected function _getColumnsForEntityLoad(\Magento\Framework\Model\AbstractModel $object, $tableName)
    {
        $fieldsetColumns = $object->getFieldset();
        if (!empty($fieldsetColumns)) {
            $readAdapter = $this->_getReadAdapter();
            if ($readAdapter instanceof \Magento\Framework\DB\Adapter\AdapterInterface) {
                $entityTableColumns = $readAdapter->describeTable($tableName);
                $columns = array_intersect($fieldsetColumns, array_keys($entityTableColumns));
            }
        }
        if (empty($columns)) {
            /** In case when fieldset was specified but no columns were matched with it, ID column is returned. */
            $columns = empty($fieldsetColumns) ? '*' : [$object->getIdFieldName()];
        }
        return $columns;
    }
}

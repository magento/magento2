<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Model\ResourceModel;

/**
 * Abstract resource model
 */
abstract class AbstractResource
{
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
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    abstract protected function getConnection();

    /**
     * Start resource transaction
     *
     * @return $this
     * @api
     */
    public function beginTransaction()
    {
        $this->getConnection()->beginTransaction();
        return $this;
    }

    /**
     * Subscribe some callback to transaction commit
     *
     * @param array $callback
     * @return $this
     * @api
     */
    public function addCommitCallback($callback)
    {
        $connectionKey = spl_object_hash($this->getConnection());
        self::$_commitCallbacks[$connectionKey][] = $callback;
        return $this;
    }

    /**
     * Commit resource transaction
     *
     * @return $this
     * @api
     */
    public function commit()
    {
        $this->getConnection()->commit();
        /**
         * Process after commit callbacks
         */
        if ($this->getConnection()->getTransactionLevel() === 0) {
            $connectionKey = spl_object_hash($this->getConnection());
            if (isset(self::$_commitCallbacks[$connectionKey])) {
                $callbacks = self::$_commitCallbacks[$connectionKey];
                self::$_commitCallbacks[$connectionKey] = [];
                try {
                    foreach ($callbacks as $callback) {
                        call_user_func($callback);
                    }
                } catch (\Exception $e) {
                    echo $e;
                    throw $e;
                }
            }
        }
        return $this;
    }

    /**
     * Roll back resource transaction
     *
     * @return $this
     * @api
     */
    public function rollBack()
    {
        $this->getConnection()->rollBack();
        $connectionKey = spl_object_hash($this->getConnection());
        self::$_commitCallbacks[$connectionKey] = [];
        return $this;
    }

    /**
     * Serialize specified field in an object
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $field
     * @param mixed $defaultValue
     * @param bool $unsetEmpty
     * @return $this
     */
    protected function _serializeField(\Magento\Framework\DataObject $object, $field, $defaultValue = null, $unsetEmpty = false)
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
     * Unserialize \Magento\Framework\DataObject field in an object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $field
     * @param mixed $defaultValue
     * @return void
     */
    protected function _unserializeField(\Magento\Framework\DataObject $object, $field, $defaultValue = null)
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
     * @param \Magento\Framework\DataObject $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(\Magento\Framework\DataObject $object, $table)
    {
        $data = [];
        $fields = $this->getConnection()->describeTable($table);
        foreach (array_keys($fields) as $field) {
            if ($object->hasData($field)) {
                $fieldValue = $object->getData($field);
                if ($fieldValue instanceof \Zend_Db_Expr) {
                    $data[$field] = $fieldValue;
                } else {
                    if (null !== $fieldValue) {
                        $fieldValue = $this->_prepareTableValueForSave($fieldValue, $fields[$field]['DATA_TYPE']);
                        $data[$field] = $this->getConnection()->prepareColumnValue($fields[$field], $fieldValue);
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
            $connection = $this->getConnection();
            if ($connection instanceof \Magento\Framework\DB\Adapter\AdapterInterface) {
                $entityTableColumns = $connection->describeTable($tableName);
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Model\CallbackPool;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Abstract resource model
 *
 * @api
 * @since 100.0.2
 */
abstract class AbstractResource
{
    /**
     * @var Json
     * @since 101.0.0
     */
    protected $serializer;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 102.0.0
     */
    protected $_logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        /**
         * Please override this one instead of overriding real __construct constructor
         */
        $this->_construct();
    }

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
    abstract public function getConnection();

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
     * @param callable|array $callback
     * @return $this
     * @api
     */
    public function addCommitCallback($callback)
    {
        CallbackPool::attach(spl_object_hash($this->getConnection()), $callback);
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
            $callbacks = CallbackPool::get(spl_object_hash($this->getConnection()));
            try {
                foreach ($callbacks as $callback) {
                    call_user_func($callback);
                }
            } catch (\Exception $e) {
                $this->getLogger()->critical($e);
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
        CallbackPool::clear(spl_object_hash($this->getConnection()));
        return $this;
    }

    /**
     * Serialize specified field in an object
     *
     * @param DataObject $object
     * @param string $field
     * @param mixed $defaultValue
     * @param bool $unsetEmpty
     * @return $this
     */
    protected function _serializeField(DataObject $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        $value = $object->getData($field);
        if (empty($value) && $unsetEmpty) {
            $object->unsetData($field);
        } else {
            $object->setData($field, $this->getSerializer()->serialize($value ?: $defaultValue));
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
    protected function _unserializeField(DataObject $object, $field, $defaultValue = null)
    {
        $value = $object->getData($field);
        if ($value) {
            $value = $this->getSerializer()->unserialize($object->getData($field));
            if (empty($value)) {
                $object->setData($field, $defaultValue);
            } else {
                $object->setData($field, $value);
            }
        } else {
            $object->setData($field, $defaultValue);
        }
    }

    /**
     * Prepare data for passed table
     *
     * @param DataObject $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(DataObject $object, $table)
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
                \Magento\Framework\Locale\FormatInterface::class
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

    /**
     * Get serializer
     *
     * @return Json
     * @deprecated 101.0.0
     * @since 101.0.0
     */
    protected function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = ObjectManager::getInstance()->get(Json::class);
        }
        return $this->serializer;
    }

    /**
     * Get logger
     *
     * @return \Psr\Log\LoggerInterface
     * @deprecated 101.0.1
     */
    private function getLogger()
    {
        if (null === $this->_logger) {
            $this->_logger = ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->_logger;
    }
}

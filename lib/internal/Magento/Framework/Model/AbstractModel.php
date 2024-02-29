<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model;

use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Abstract model class
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
abstract class AbstractModel extends DataObject
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_abstract';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'object';

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Data changes flag (true after setData|unsetData call)
     * @var bool
     */
    protected $_hasDataChanges = false;

    /**
     * Original data that was loaded
     *
     * @var array
     */
    protected $_origData;

    /**
     * Object delete flag
     *
     * @var bool
     */
    protected $_isDeleted = false;

    /**
     * Resource model instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected $_resourceCollection;

    /**
     * Name of the resource model
     *
     * @var string
     */
    protected $_resourceName;

    /**
     * Name of the resource collection model
     *
     * @var string
     */
    protected $_collectionName;

    /**
     * Model cache tag for clear cache in after save and after delete
     *
     * When you use true - all cache will be clean
     *
     * @var string|array|bool
     */
    protected $_cacheTag = false;

    /**
     * Flag which can stop data saving after before save
     * Can be used for next sequence: we check data in _beforeSave, if data are
     * not valid - we can set this flag to false value and save process will be stopped
     *
     * @var bool
     */
    protected $_dataSaveAllowed = true;

    /**
     * Flag which allow detect object state: is it new object (without id) or existing one (with id)
     *
     * @var bool
     */
    protected $_isObjectNew = null;

    /**
     * Validator for checking the model state before saving it
     *
     * @var ValidatorInterface|bool|null
     */
    protected $_validatorBeforeSave = null;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Application Cache Manager
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\Model\ActionValidator\RemoveAction
     */
    protected $_actionValidator;

    /**
     * Array to store object's original data
     *
     * @var array
     */
    protected $storedData = [];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_registry = $registry;
        $this->_appState = $context->getAppState();
        $this->_eventManager = $context->getEventDispatcher();
        $this->_cacheManager = $context->getCacheManager();
        $this->_resource = $resource;
        $this->_resourceCollection = $resourceCollection;
        $this->_logger = $context->getLogger();
        $this->_actionValidator = $context->getActionValidator();

        if ($this->_resource !== null
            && (method_exists($this->_resource, 'getIdFieldName') || ($this->_resource instanceof DataObject))
        ) {
            $this->_idFieldName = $this->_getResource()->getIdFieldName();
        }

        parent::__construct($data);
        $this->_construct();
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct() //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
    {
    }

    /**
     * Standard model initialization
     *
     * @param string $resourceModel
     * @return void
     */
    protected function _init($resourceModel)
    {
        $this->_setResourceModel($resourceModel);
        $this->_idFieldName = $this->_getResource()->getIdFieldName();
    }

    /**
     * Remove unneeded properties from serialization
     *
     * @return string[]
     */
    public function __sleep()
    {
        $properties = array_keys(get_object_vars($this));
        $properties = array_diff(
            $properties,
            [
                '_eventManager',
                '_cacheManager',
                '_registry',
                '_appState',
                '_actionValidator',
                '_logger',
                '_resourceCollection',
                '_resource',
            ]
        );
        return $properties;
    }

    /**
     * Init not serializable fields
     *
     * @return void
     */
    public function __wakeup()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_registry = $objectManager->get(\Magento\Framework\Registry::class);

        $context = $objectManager->get(\Magento\Framework\Model\Context::class);
        if ($context instanceof \Magento\Framework\Model\Context) {
            $this->_appState = $context->getAppState();
            $this->_eventManager = $context->getEventDispatcher();
            $this->_cacheManager = $context->getCacheManager();
            $this->_logger = $context->getLogger();
            $this->_actionValidator = $context->getActionValidator();
        }
    }

    /**
     * Id field name setter
     *
     * @param  string $name
     * @return $this
     */
    public function setIdFieldName($name)
    {
        $this->_idFieldName = $name;
        return $this;
    }

    /**
     * Id field name getter
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    /**
     * Identifier getter
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_getData($this->_idFieldName);
    }

    /**
     * Identifier setter
     *
     * @param mixed $value
     * @return $this
     */
    public function setId($value)
    {
        $this->setData($this->_idFieldName, $value);
        return $this;
    }

    /**
     * Set _isDeleted flag value (if $isDeleted parameter is defined) and return current flag value
     *
     * @param boolean $isDeleted
     * @return bool
     */
    public function isDeleted($isDeleted = null)
    {
        $result = $this->_isDeleted;
        if ($isDeleted !== null) {
            $this->_isDeleted = $isDeleted;
        }
        return $result;
    }

    /**
     * Check if initial object data was changed.
     *
     * Initial data is coming to object constructor.
     * Flag value should be set up to true after any external data changes
     *
     * @return bool
     */
    public function hasDataChanges()
    {
        return $this->_hasDataChanges;
    }

    /**
     * Overwrite data in the object.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if ($key === (array)$key) {
            if ($this->_data !== $key) {
                $this->_hasDataChanges = true;
            }
            $this->_data = $key;
        } else {
            if (!array_key_exists($key, $this->_data) || $this->_data[$key] !== $value) {
                $this->_hasDataChanges = true;
            }
            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * Unset data from the object.
     *
     * @param null|string|array $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->setData([]);
        } elseif (is_string($key)) {
            if (isset($this->_data[$key]) || array_key_exists($key, $this->_data)) {
                $this->_hasDataChanges = true;
                unset($this->_data[$key]);
            }
        } elseif ($key === (array)$key) {
            foreach ($key as $element) {
                $this->unsetData($element);
            }
        }
        return $this;
    }

    /**
     * Clears data changes status
     *
     * @param bool $value
     * @return $this
     */
    public function setDataChanges($value)
    {
        $this->_hasDataChanges = (bool)$value;
        return $this;
    }

    /**
     * Get object original data
     *
     * @param string $key
     * @return mixed
     */
    public function getOrigData($key = null)
    {
        if ($key === null) {
            return $this->_origData;
        }
        if (isset($this->_origData[$key])) {
            return $this->_origData[$key];
        }
        return null;
    }

    /**
     * Initialize object original data
     *
     * @FIXME changing original data can't be available as public interface
     *
     * @param string $key
     * @param mixed $data
     * @return $this
     */
    public function setOrigData($key = null, $data = null)
    {
        if ($key === null) {
            $this->_origData = $this->_data;
        } else {
            $this->_origData[$key] = $data;
        }
        return $this;
    }

    /**
     * Compare object data with original data
     *
     * @param string $field
     * @return bool
     */
    public function dataHasChangedFor($field)
    {
        $newData = $this->getData($field);
        $origData = $this->getOrigData($field);
        return $newData != $origData;
    }

    /**
     * Set resource names
     *
     * If collection name is omitted, resource name will be used with _collection appended
     *
     * @param string $resourceName
     * @param string|null $collectionName
     * @return void
     */
    protected function _setResourceModel($resourceName, $collectionName = null)
    {
        $this->_resourceName = $resourceName;
        if ($collectionName === null) {
            $collectionName = $resourceName . '\\' . 'Collection';
        }
        $this->_collectionName = $collectionName;
    }

    /**
     * Get resource instance
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @deprecated 101.0.0 because resource models should be used directly
     * @see we don't recommend this approach anymore
     */
    protected function _getResource()
    {
        if (empty($this->_resourceName) && empty($this->_resource)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('The resource isn\'t set.')
            );
        }
        return $this->_resource ?: \Magento\Framework\App\ObjectManager::getInstance()->get($this->_resourceName);
    }

    /**
     * Retrieve model resource name
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->_resource ? get_class($this->_resource) : ($this->_resourceName ? $this->_resourceName : null);
    }

    /**
     * Get collection instance
     *
     * @TODO MAGETWO-23541: Incorrect dependencies between Model\AbstractModel and Data\Collection\Db from Framework
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @deprecated 101.0.0 because collections should be used directly via factory
     * @see we don't recommend this approach anymore
     */
    public function getResourceCollection()
    {
        if (empty($this->_resourceCollection) && empty($this->_collectionName)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Model collection resource name is not defined.')
            );
        }
        return !$this->_collectionName ? clone $this
            ->_resourceCollection : \Magento\Framework\App\ObjectManager::getInstance()
            ->create(
                $this->_collectionName
            );
    }

    /**
     * Retrieve collection instance
     *
     * @TODO MAGETWO-23541: Incorrect dependencies between Model\AbstractModel and Data\Collection\Db from Framework
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @deprecated 101.0.0 because collections should be used directly via factory
     * @see we don't recommend this approach anymore
     */
    public function getCollection()
    {
        return $this->getResourceCollection();
    }

    /**
     * Load object data
     *
     * @param integer $modelId
     * @param null|string $field
     * @return $this
     * @deprecated 100.1.0 because entities must not be responsible for their own loading.
     * Service contracts should persist entities. Use resource model "load" or collections to implement
     * service contract model loading operations.
     * @see we don't recommend this approach anymore
     */
    public function load($modelId, $field = null)
    {
        $this->_getResource()->load($this, $modelId, $field);
        return $this;
    }

    /**
     * Get array of objects transferred to default events processing
     *
     * @return array
     */
    protected function _getEventData()
    {
        return [
            'data_object' => $this,
            $this->_eventObject => $this,
        ];
    }

    /**
     * Processing object before load data
     *
     * @param int $modelId
     * @param null|string $field
     * @return $this
     */
    protected function _beforeLoad($modelId, $field = null)
    {
        $params = ['object' => $this, 'field' => $field, 'value' => $modelId];
        $this->_eventManager->dispatch('model_load_before', $params);
        $params = array_merge($params, $this->_getEventData());
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_before', $params);
        return $this;
    }

    /**
     * Processing object after load data
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->_eventManager->dispatch('model_load_after', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', $this->_getEventData());
        return $this;
    }

    /**
     * Process operation before object load
     *
     * @param string $identifier
     * @param string|null $field
     * @return void
     * @since 101.0.0
     */
    public function beforeLoad($identifier, $field = null)
    {
        $this->_beforeLoad($identifier, $field);
    }

    /**
     * Object after load processing. Implemented as public interface for supporting objects after load in collections
     *
     * @return $this
     */
    public function afterLoad()
    {
        $this->_afterLoad();
        $this->updateStoredData();
        return $this;
    }

    /**
     * Check whether model has changed data.
     * Can be overloaded in child classes to perform advanced check whether model needs to be saved
     * e.g. using resourceModel->hasDataChanged() or any other technique
     *
     * @return boolean
     */
    protected function _hasModelChanged()
    {
        return $this->hasDataChanges();
    }

    /**
     * Check if save is allowed
     *
     * @return bool
     */
    public function isSaveAllowed()
    {
        return (bool) $this->_dataSaveAllowed;
    }

    /**
     * Set flag property _hasDataChanges
     *
     * @param bool $flag
     * @return void
     */
    public function setHasDataChanges($flag)
    {
        $this->_hasDataChanges = $flag;
    }

    /**
     * Save object data
     *
     * @return $this
     * @throws \Exception
     *
     * @deprecated 100.1.0 because entities must not be responsible for their own persistence.
     * Service contracts should persist entities. Use resource model "save" to implement
     * service contract persistence operations.
     * @see we don't recommend this approach anymore
     */
    public function save()
    {
        $this->_getResource()->save($this);
        return $this;
    }

    /**
     * Callback function which called after transaction commit in resource model
     *
     * @return $this
     */
    public function afterCommitCallback()
    {
        $this->_eventManager->dispatch('model_save_commit_after', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_save_commit_after', $this->_getEventData());
        return $this;
    }

    /**
     * Check object state (true - if it is object without id on object just created)
     * This method can help detect if object just created in _afterSave method
     * problem is what in after save object has id and we can't detect what object was
     * created in this transaction
     *
     * @param bool|null $flag
     * @return bool
     */
    public function isObjectNew($flag = null)
    {
        if ($flag !== null) {
            $this->_isObjectNew = $flag;
        }
        if ($this->_isObjectNew !== null) {
            return $this->_isObjectNew;
        }
        return !(bool)$this->getId();
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        if (!$this->getId()) {
            $this->isObjectNew(true);
        }
        $this->_eventManager->dispatch('model_save_before', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_save_before', $this->_getEventData());
        return $this;
    }

    /**
     * Validate model before saving it
     *
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function validateBeforeSave()
    {
        $validator = $this->_getValidatorBeforeSave();
        if ($validator && !$validator->isValid($this)) {
            $errors = $validator->getMessages();
            $exception = new \Magento\Framework\Validator\Exception(
                new Phrase(implode(PHP_EOL, $errors))
            );
            foreach ($errors as $errorMessage) {
                $exception->addMessage(new \Magento\Framework\Message\Error($errorMessage));
            }
            throw $exception;
        }
        return $this;
    }

    /**
     * Returns validator, which contains all rules to validate this model.
     *
     * Returns FALSE, if no validation rules exist.
     *
     * @return ValidatorInterface|false
     */
    protected function _getValidatorBeforeSave()
    {
        if ($this->_validatorBeforeSave === null) {
            $this->_validatorBeforeSave = $this->_createValidatorBeforeSave();
        }
        return $this->_validatorBeforeSave;
    }

    /**
     * Creates validator for the model with all validation rules in it.
     *
     * Returns FALSE, if no validation rules exist.
     *
     * @return ValidatorInterface|bool
     * @throws LocalizedException
     */
    protected function _createValidatorBeforeSave()
    {
        $modelRules = $this->_getValidationRulesBeforeSave();
        $resourceRules = $this->_getResource()->getValidationRulesBeforeSave();
        if (!$modelRules && !$resourceRules) {
            return false;
        }

        $validator = $this->getValidator();
        if ($modelRules) {
            $validator->attach($modelRules);
        }
        if ($resourceRules) {
            $validator->attach($resourceRules);
        }

        return $validator;
    }

    /**
     * Create validator instance
     *
     * @return ValidatorChain
     */
    private function getValidator(): ValidatorChain
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create(ValidatorChain::class);
    }

    /**
     * Template method to return validate rules for the entity
     *
     * @return ValidatorInterface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        return null;
    }

    /**
     * Get list of cache tags applied to model object.
     *
     * Return false if cache tags are not supported by model
     *
     * @return array|false
     */
    public function getCacheTags()
    {
        $tags = false;
        if ($this->_cacheTag) {
            if ($this->_cacheTag === true) {
                $tags = [];
            } else {
                if (is_array($this->_cacheTag)) {
                    $tags = $this->_cacheTag;
                } else {
                    $tags = [$this->_cacheTag];
                }
            }
        }
        return $tags;
    }

    /**
     * Remove model object related cache
     *
     * @return $this
     */
    public function cleanModelCache()
    {
        $tags = $this->getCacheTags();
        if (!empty($tags)) {
            $this->_cacheManager->clean($tags);
        }
        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        $this->cleanModelCache();
        $this->_eventManager->dispatch('model_save_after', ['object' => $this]);
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_save_after', $this->_getEventData());
        $this->updateStoredData();
        return $this;
    }

    /**
     * Delete object from database
     *
     * @return $this
     * @throws \Exception
     * @deprecated 100.1.0 because entities must not be responsible for their own deletion.
     * Service contracts should delete entities. Use resource model "delete" method to implement
     * service contract persistence operations.
     * @see we don't recommend this approach anymore
     */
    public function delete()
    {
        $this->_getResource()->delete($this);
        return $this;
    }

    /**
     * Processing object before delete data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        if (!$this->_actionValidator->isAllowed($this)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase('Delete operation is forbidden for current area')
            );
        }

        $this->_eventManager->dispatch('model_delete_before', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_before', $this->_getEventData());
        $this->cleanModelCache();
        return $this;
    }

    /**
     * Processing object after delete data
     *
     * @return $this
     */
    public function afterDelete()
    {
        $this->_eventManager->dispatch('model_delete_after', ['object' => $this]);
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_after', $this->_getEventData());
        $this->storedData = [];
        return $this;
    }

    /**
     * Processing manipulation after main transaction commit
     *
     * @return $this
     */
    public function afterDeleteCommit()
    {
        $this->_eventManager->dispatch('model_delete_commit_after', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_commit_after', $this->_getEventData());
        return $this;
    }

    /**
     * Retrieve model resource
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @deprecated 101.0.0 because resource models should be used directly
     * @see we don't recommend this approach anymore
     */
    public function getResource()
    {
        return $this->_getResource();
    }

    /**
     * Retrieve entity id
     *
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->_getData('entity_id');
    }

    /**
     * Set entity id
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData('entity_id', $entityId);
    }

    /**
     * Clearing object for correct deleting by garbage collector
     *
     * @return $this
     */
    public function clearInstance()
    {
        $this->_clearReferences();
        $this->_eventManager->dispatch($this->_eventPrefix . '_clear', $this->_getEventData());
        $this->_clearData();
        return $this;
    }

    /**
     * Clearing cyclic references
     *
     * @return $this
     */
    protected function _clearReferences()
    {
        return $this;
    }

    /**
     * Clearing object's data
     *
     * @return $this
     */
    protected function _clearData()
    {
        return $this;
    }

    /**
     * Synchronize object's stored data with the actual data
     *
     * @return $this
     */
    private function updateStoredData()
    {
        if (isset($this->_data)) {
            $this->storedData = $this->_data;
        } else {
            $this->storedData = [];
        }
        return $this;
    }

    /**
     * Model StoredData getter
     *
     * @return array
     */
    public function getStoredData()
    {
        return $this->storedData;
    }

    /**
     * Returns _eventPrefix
     *
     * @return string
     */
    public function getEventPrefix()
    {
        return $this->_eventPrefix;
    }
}

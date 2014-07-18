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
namespace Magento\Framework\Model;

/**
 * Abstract model class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractModel extends \Magento\Framework\Object
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
     * Resource model instance
     *
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected $_resource;

    /**
     * Resource collection
     *
     * @var \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
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
     * @var \Zend_Validate_Interface|bool|null
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
     * @var \Magento\Framework\Logger
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
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_registry = $registry;
        $this->_appState = $context->getAppState();
        $this->_eventManager = $context->getEventDispatcher();
        $this->_cacheManager = $context->getCacheManager();
        $this->_resource = $resource;
        $this->_resourceCollection = $resourceCollection;
        $this->_logger = $context->getLogger();
        $this->_actionValidator = $context->getActionValidator();

        if (method_exists($this->_resource, 'getIdFieldName')
            || $this->_resource instanceof \Magento\Framework\Object
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
    protected function _construct()
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
     * @return string[]
     */
    public function __sleep()
    {
        $properties = array_keys(get_object_vars($this));
        $properties = array_diff($properties, array('_eventManager', '_cacheManager', '_registry', '_appState'));
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
        $this->_eventManager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
        $this->_cacheManager = $objectManager->get('Magento\Framework\App\CacheInterface');
        $this->_registry = $objectManager->get('Magento\Framework\Registry');
        $context = $objectManager->get('Magento\Framework\Model\Context');
        if ($context instanceof \Magento\Framework\Model\Context) {
            $this->_appState = $context->getAppState();
        }
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
        if (is_null($collectionName)) {
            $collectionName = $resourceName . \Magento\Framework\Autoload\IncludePath::NS_SEPARATOR . 'Collection';
        }
        $this->_collectionName = $collectionName;
    }

    /**
     * Get resource instance
     *
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
     */
    protected function _getResource()
    {
        if (empty($this->_resourceName) && empty($this->_resource)) {
            throw new \Magento\Framework\Model\Exception(__('Resource is not set.'));
        }

        return $this->_resource ? : \Magento\Framework\App\ObjectManager::getInstance()->get($this->_resourceName);
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
     * @deplacated
     * @throws \Magento\Framework\Model\Exception
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    public function getResourceCollection()
    {
        if (empty($this->_resourceCollection) && empty($this->_collectionName)) {
            throw new \Magento\Framework\Model\Exception(__('Model collection resource name is not defined.'));
        }
        return $this->_resourceCollection ? clone $this
            ->_resourceCollection : \Magento\Framework\App\ObjectManager::getInstance()
            ->create(
                $this->_collectionName
            );
    }

    /**
     * Retrieve collection instance
     *
     * @deprecated
     * @return \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
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
     */
    public function load($modelId, $field = null)
    {
        $this->_beforeLoad($modelId, $field);
        $this->_getResource()->load($this, $modelId, $field);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }

    /**
     * Get array of objects transferred to default events processing
     *
     * @return array
     */
    protected function _getEventData()
    {
        return array(
            'data_object' => $this,
            $this->_eventObject => $this,
        );
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
        $params = array('object' => $this, 'field' => $field, 'value' => $modelId);
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
        $this->_eventManager->dispatch('model_load_after', array('object' => $this));
        $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', $this->_getEventData());
        return $this;
    }

    /**
     * Object after load processing. Implemented as public interface for supporting objects after load in collections
     *
     * @return $this
     */
    public function afterLoad()
    {
        $this->getResource()->afterLoad($this);
        $this->_afterLoad();
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
     * Save object data
     *
     * @return $this
     * @throws \Exception
     */
    public function save()
    {
        /**
         * Direct deleted items to delete method
         */
        if ($this->isDeleted()) {
            return $this->delete();
        }
        if (!$this->_hasModelChanged()) {
            return $this;
        }
        $this->_getResource()->beginTransaction();
        try {
            $this->_validateBeforeSave();
            $this->_beforeSave();
            if ($this->_dataSaveAllowed) {
                $this->_getResource()->save($this);
                $this->_afterSave();
            }
            $this->_getResource()->addCommitCallback(array($this, 'afterCommitCallback'))->commit();
            $this->_hasDataChanges = false;
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            $this->_hasDataChanges = true;
            throw $e;
        }
        return $this;
    }

    /**
     * Callback function which called after transaction commit in resource model
     *
     * @return $this
     */
    public function afterCommitCallback()
    {
        $this->_eventManager->dispatch('model_save_commit_after', array('object' => $this));
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
    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->isObjectNew(true);
        }
        $this->_eventManager->dispatch('model_save_before', array('object' => $this));
        $this->_eventManager->dispatch($this->_eventPrefix . '_save_before', $this->_getEventData());
        return $this;
    }

    /**
     * Validate model before saving it
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _validateBeforeSave()
    {
        $validator = $this->_getValidatorBeforeSave();
        if ($validator && !$validator->isValid($this)) {
            $errors = $validator->getMessages();
            $exception = new \Magento\Framework\Model\Exception(implode(PHP_EOL, $errors));
            foreach ($errors as $errorMessage) {
                $exception->addMessage(new \Magento\Framework\Message\Error($errorMessage));
            }
            throw $exception;
        }
        return $this;
    }

    /**
     * Returns validator, which contains all rules to validate this model.
     * Returns FALSE, if no validation rules exist.
     *
     * @return \Zend_Validate_Interface|false
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
     * Returns FALSE, if no validation rules exist.
     *
     * @return \Zend_Validate_Interface|bool
     */
    protected function _createValidatorBeforeSave()
    {
        $modelRules = $this->_getValidationRulesBeforeSave();
        $resourceRules = $this->_getResource()->getValidationRulesBeforeSave();
        if (!$modelRules && !$resourceRules) {
            return false;
        }

        if ($modelRules && $resourceRules) {
            $validator = new \Zend_Validate();
            $validator->addValidator($modelRules);
            $validator->addValidator($resourceRules);
        } elseif ($modelRules) {
            $validator = $modelRules;
        } else {
            $validator = $resourceRules;
        }

        return $validator;
    }

    /**
     * Template method to return validate rules for the entity
     *
     * @return \Zend_Validate_Interface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        return null;
    }

    /**
     * Get list of cache tags applied to model object.
     * Return false if cache tags are not supported by model
     *
     * @return array|false
     */
    public function getCacheTags()
    {
        $tags = false;
        if ($this->_cacheTag) {
            if ($this->_cacheTag === true) {
                $tags = array();
            } else {
                if (is_array($this->_cacheTag)) {
                    $tags = $this->_cacheTag;
                } else {
                    $tags = array($this->_cacheTag);
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
        if ($tags !== false) {
            $this->_cacheManager->clean($tags);
        }
        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    protected function _afterSave()
    {
        $this->cleanModelCache();
        $this->_eventManager->dispatch('model_save_after', array('object' => $this));
        $this->_eventManager->dispatch('clean_cache_by_tags', array('object' => $this));
        $this->_eventManager->dispatch($this->_eventPrefix . '_save_after', $this->_getEventData());
        return $this;
    }

    /**
     * Delete object from database
     *
     * @return $this
     * @throws \Exception
     */
    public function delete()
    {
        $this->_getResource()->beginTransaction();
        try {
            $this->_beforeDelete();
            $this->_getResource()->delete($this);
            $this->isDeleted(true);
            $this->_afterDelete();

            $this->_getResource()->commit();
            $this->_afterDeleteCommit();
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Processing object before delete data
     *
     * @return $this
     * @throws Exception
     */
    protected function _beforeDelete()
    {
        if (!$this->_actionValidator->isAllowed($this)) {
            throw new Exception(__('Delete operation is forbidden for current area'));
        }

        $this->_eventManager->dispatch('model_delete_before', array('object' => $this));
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_before', $this->_getEventData());
        $this->cleanModelCache();
        return $this;
    }

    /**
     * Processing object after delete data
     *
     * @return $this
     */
    protected function _afterDelete()
    {
        $this->_eventManager->dispatch('model_delete_after', array('object' => $this));
        $this->_eventManager->dispatch('clean_cache_by_tags', array('object' => $this));
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_after', $this->_getEventData());
        return $this;
    }

    /**
     * Processing manipulation after main transaction commit
     *
     * @return $this
     */
    protected function _afterDeleteCommit()
    {
        $this->_eventManager->dispatch('model_delete_commit_after', array('object' => $this));
        $this->_eventManager->dispatch($this->_eventPrefix . '_delete_commit_after', $this->_getEventData());
        return $this;
    }

    /**
     * Retrieve model resource
     *
     * @return \Magento\Framework\Model\Resource\Db\AbstractDb
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
}

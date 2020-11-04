<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity;

/**
 * Entity type model
 *
 * @api
 * @method \Magento\Eav\Model\Entity\Type setEntityTypeCode(string $value)
 * @method string getEntityModel()
 * @method \Magento\Eav\Model\Entity\Type setEntityModel(string $value)
 * @method \Magento\Eav\Model\Entity\Type setAttributeModel(string $value)
 * @method \Magento\Eav\Model\Entity\Type setEntityTable(string $value)
 * @method \Magento\Eav\Model\Entity\Type setValueTablePrefix(string $value)
 * @method \Magento\Eav\Model\Entity\Type setEntityIdField(string $value)
 * @method int getIsDataSharing()
 * @method \Magento\Eav\Model\Entity\Type setIsDataSharing(int $value)
 * @method string getDataSharingKey()
 * @method \Magento\Eav\Model\Entity\Type setDataSharingKey(string $value)
 * @method \Magento\Eav\Model\Entity\Type setDefaultAttributeSetId(int $value)
 * @method string getIncrementModel()
 * @method \Magento\Eav\Model\Entity\Type setIncrementModel(string $value)
 * @method int getIncrementPerStore()
 * @method \Magento\Eav\Model\Entity\Type setIncrementPerStore(int $value)
 * @method int getIncrementPadLength()
 * @method \Magento\Eav\Model\Entity\Type setIncrementPadLength(int $value)
 * @method string getIncrementPadChar()
 * @method \Magento\Eav\Model\Entity\Type setIncrementPadChar(string $value)
 * @method string getAdditionalAttributeTable()
 * @method \Magento\Eav\Model\Entity\Type setAdditionalAttributeTable(string $value)
 * @method \Magento\Eav\Model\Entity\Type setEntityAttributeCollection(string $value)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Type extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Collection of attributes
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $_attributes;

    /**
     * Array of attributes
     *
     * @var array
     */
    protected $_attributesBySet = [];

    /**
     * Collection of sets
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    protected $_sets;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_attSetFactory;

    /**
     * @var \Magento\Eav\Model\Entity\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attSetFactory
     * @param \Magento\Eav\Model\Entity\StoreFactory $storeFactory
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attSetFactory,
        \Magento\Eav\Model\Entity\StoreFactory $storeFactory,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_attributeFactory = $attributeFactory;
        $this->_attSetFactory = $attSetFactory;
        $this->_storeFactory = $storeFactory;
        $this->_universalFactory = $universalFactory;
    }

    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(\Magento\Eav\Model\ResourceModel\Entity\Type::class);
    }

    /**
     * Load type by code
     *
     * @param string $code
     * @return $this
     */
    public function loadByCode($code)
    {
        $this->_getResource()->loadByCode($this, $code);
        $this->_afterLoad();
        return $this;
    }

    /**
     * Retrieve entity type attributes collection
     *
     * @param   int $setId
     * @return  \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getAttributeCollection($setId = null)
    {
        if ($setId === null) {
            if ($this->_attributes === null) {
                $this->_attributes = $this->_getAttributeCollection()->setEntityTypeFilter($this);
            }
            $collection = $this->_attributes;
        } else {
            if (!isset($this->_attributesBySet[$setId])) {
                $this->_attributesBySet[$setId] = $this->_getAttributeCollection()->setEntityTypeFilter(
                    $this
                )->setAttributeSetFilter(
                    $setId
                );
            }
            $collection = $this->_attributesBySet[$setId];
        }

        return $collection;
    }

    /**
     * Init and retrieve attribute collection
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected function _getAttributeCollection()
    {
        $collection = $this->_universalFactory->create($this->getEntityAttributeCollection());
        $collection->setItemObjectClass($this->getAttributeModel());
        return $collection;
    }

    /**
     * Retrieve entity tpe sets collection
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    public function getAttributeSetCollection()
    {
        if (empty($this->_sets)) {
            $this->_sets = $this->_attSetFactory->create()->getResourceCollection()->setEntityTypeFilter(
                $this->getId()
            );
        }
        return $this->_sets;
    }

    /**
     * Retrieve new incrementId
     *
     * @param int $storeId
     * @return string
     * @throws \Exception
     */
    public function fetchNewIncrementId($storeId = null)
    {
        if (!$this->getIncrementModel()) {
            return false;
        }

        if (!$this->getIncrementPerStore() || $storeId === null) {
            /**
             * store_id null we can have for entity from removed store
             */
            $storeId = 0;
        }

        // Start transaction to run SELECT ... FOR UPDATE
        $this->_getResource()->beginTransaction();

        try {
            $entityStoreConfig = $this->_storeFactory->create()->loadByEntityStore($this->getId(), $storeId);

            if (!$entityStoreConfig->getId()) {
                $entityStoreConfig->setEntityTypeId(
                    $this->getId()
                )->setStoreId(
                    $storeId
                )->setIncrementPrefix(
                    $storeId
                )->save();
            }

            $incrementInstance = $this->_universalFactory->create(
                $this->getIncrementModel()
            )->setPrefix(
                $entityStoreConfig->getIncrementPrefix()
            )->setPadLength(
                $this->getIncrementPadLength()
            )->setPadChar(
                $this->getIncrementPadChar()
            )->setLastId(
                $entityStoreConfig->getIncrementLastId()
            )->setEntityTypeId(
                $entityStoreConfig->getEntityTypeId()
            )->setStoreId(
                $entityStoreConfig->getStoreId()
            );

            /**
             * do read lock on eav/entity_store to solve potential timing issues
             * (most probably already done by beginTransaction of entity save)
             */
            $incrementId = $incrementInstance->getNextId();
            $entityStoreConfig->setIncrementLastId($incrementId);
            $entityStoreConfig->save();

            // Commit increment_last_id changes
            $this->_getResource()->commit();
        } catch (\Exception $exception) {
            $this->_getResource()->rollBack();
            throw $exception;
        }

        return $incrementId;
    }

    /**
     * Retrieve entity id field
     *
     * @return string|null
     */
    public function getEntityIdField()
    {
        return $this->_data['entity_id_field'] ?? null;
    }

    /**
     * Retrieve entity table name
     *
     * @return string|null
     */
    public function getEntityTable()
    {
        if (isset($this->_data['entity_table'])) {
            return $this->getResource()->getTable($this->_data['entity_table']);
        }

        return null;
    }

    /**
     * Retrieve entity table prefix name
     *
     * @return null|string
     */
    public function getValueTablePrefix()
    {
        $prefix = $this->getEntityTablePrefix();
        if ($prefix) {
            return $this->getResource()->getTable($prefix);
        }

        return null;
    }

    /**
     * Retrieve entity table prefix
     *
     * @return string
     */
    public function getEntityTablePrefix()
    {
        $tablePrefix = trim($this->_data['value_table_prefix']);

        if (empty($tablePrefix)) {
            $tablePrefix = $this->getEntityTable();
        }

        return $tablePrefix;
    }

    /**
     * Get default attribute set identifier for entity type
     *
     * @return string|null
     */
    public function getDefaultAttributeSetId()
    {
        return $this->_data['default_attribute_set_id'] ?? null;
    }

    /**
     * Retrieve entity type id
     *
     * @return string|null
     */
    public function getEntityTypeId()
    {
        return $this->_data['entity_type_id'] ?? null;
    }

    /**
     * Retrieve entity type code
     *
     * @return string|null
     */
    public function getEntityTypeCode()
    {
        return $this->_data['entity_type_code'] ?? null;
    }

    /**
     * Get attribute model code for entity type
     *
     * @return string
     */
    public function getAttributeModel()
    {
        if (empty($this->_data['attribute_model'])) {
            return \Magento\Eav\Model\Entity::DEFAULT_ATTRIBUTE_MODEL;
        }

        return $this->_data['attribute_model'];
    }

    /**
     * Retrieve resource entity object
     *
     * @return \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    public function getEntity()
    {
        return $this->_universalFactory->create($this->_data['entity_model']);
    }

    /**
     * Return attribute collection. If not specify return default
     *
     * @return string
     */
    public function getEntityAttributeCollection()
    {
        $collection = $this->_getData('entity_attribute_collection');
        if ($collection) {
            return $collection;
        }
        return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection::class;
    }
}

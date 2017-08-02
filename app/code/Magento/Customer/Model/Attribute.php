<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Customer attribute model
 *
 * @method int getSortOrder()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Attribute extends \Magento\Eav\Model\Attribute
{
    /**
     * Name of the module
     */
    const MODULE_NAME = 'Magento_Customer';

    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'customer_entity_attribute';

    /**
     * Prefix of model events object
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'attribute';

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     * @since 2.0.0
     */
    protected $indexerRegistry;

    /**
     * @var \Magento\Customer\Model\Metadata\AttributeMetadataCache
     * @since 2.2.0
     */
    private $attributeMetadataCache;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array|null $data
     * @param \Magento\Customer\Model\Metadata\AttributeMetadataCache|null $attributeMetadataCache
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Customer\Model\Metadata\AttributeMetadataCache $attributeMetadataCache = null
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->attributeMetadataCache = $attributeMetadataCache ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Customer\Model\Metadata\AttributeMetadataCache::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper,
            $localeDate,
            $reservedAttributeList,
            $localeResolver,
            $dateTimeFormatter,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Init resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Customer\Model\ResourceModel\Attribute::class);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function afterSave()
    {
        if ($this->isObjectNew() && (bool)$this->getData(EavAttributeInterface::IS_USED_IN_GRID)) {
            $this->_getResource()->addCommitCallback([$this, 'invalidate']);
        } elseif (!$this->isObjectNew() && $this->dataHasChangedFor(EavAttributeInterface::IS_USED_IN_GRID)) {
            $this->_getResource()->addCommitCallback([$this, 'invalidate']);
        }
        $this->attributeMetadataCache->clean();
        return parent::afterSave();
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function afterDelete()
    {
        $this->attributeMetadataCache->clean();
        return parent::afterDelete();
    }

    /**
     * Init indexing process after customer delete
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    public function afterDeleteCommit()
    {
        if ($this->getData(EavAttributeInterface::IS_USED_IN_GRID) == true) {
            $this->invalidate();
        }
        return parent::afterDeleteCommit();
    }

    /**
     * Init indexing process after customer save
     *
     * @return void
     * @since 2.0.0
     */
    public function invalidate()
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->invalidate();
    }

    /**
     * Check whether attribute is searchable in admin grid and it is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function canBeSearchableInGrid()
    {
        return $this->getData('is_searchable_in_grid') && in_array($this->getFrontendInput(), ['text', 'textarea']);
    }

    /**
     * Check whether attribute is filterable in admin grid and it is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function canBeFilterableInGrid()
    {
        return $this->getData('is_filterable_in_grid')
            && in_array($this->getFrontendInput(), ['text', 'date', 'select', 'boolean']);
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function __sleep()
    {
        $this->unsetData('entity_type');
        return array_diff(
            parent::__sleep(),
            ['indexerRegistry', '_website']
        );
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->indexerRegistry = $objectManager->get(\Magento\Framework\Indexer\IndexerRegistry::class);
    }
}

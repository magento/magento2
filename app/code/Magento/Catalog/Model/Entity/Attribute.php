<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Entity;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Product attribute extension with event dispatching
 *
 * @method \Magento\Catalog\Model\ResourceModel\Attribute _getResource()
 * @method \Magento\Catalog\Model\ResourceModel\Attribute getResource()
 * @method string getFrontendInputRenderer()
 * @method \Magento\Catalog\Model\Entity\Attribute setFrontendInputRenderer(string $value)
 * @method int setIsGlobal(int $value)
 * @method int getIsVisible()
 * @method int setIsVisible(int $value)
 * @method int getIsSearchable()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsSearchable(int $value)
 * @method int getSearchWeight()
 * @method \Magento\Catalog\Model\Entity\Attribute setSearchWeight(int $value)
 * @method int getIsFilterable()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsFilterable(int $value)
 * @method int getIsComparable()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsComparable(int $value)
 * @method \Magento\Catalog\Model\Entity\Attribute setIsVisibleOnFront(int $value)
 * @method int getIsHtmlAllowedOnFront()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsHtmlAllowedOnFront(int $value)
 * @method int getIsUsedForPriceRules()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsUsedForPriceRules(int $value)
 * @method int getIsFilterableInSearch()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsFilterableInSearch(int $value)
 * @method \Magento\Catalog\Model\Entity\Attribute setUsedInProductListing(int $value)
 * @method \Magento\Catalog\Model\Entity\Attribute setUsedForSortBy(int $value)
 * @method \Magento\Catalog\Model\Entity\Attribute setApplyTo(string $value)
 * @method int getIsVisibleInAdvancedSearch()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsVisibleInAdvancedSearch(int $value)
 * @method \Magento\Catalog\Model\Entity\Attribute setPosition(int $value)
 * @method int getIsWysiwygEnabled()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsWysiwygEnabled(int $value)
 * @method int getIsUsedForPromoRules()
 * @method \Magento\Catalog\Model\Entity\Attribute setIsUsedForPromoRules(int $value)
 * @method \Magento\Eav\Api\Data\AttributeExtensionInterface getExtensionAttributes()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute
{
    /**
     * Event Prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'catalog_entity_attribute';

    /**
     * Event Object
     *
     * @var string
     */
    protected $_eventObject = 'attribute';

    const MODULE_NAME = 'Magento_Catalog';

    /**
     * @var LockValidatorInterface
     */
    protected $attrLockValidator;

    /**
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
     * @param LockValidatorInterface $lockValidator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        LockValidatorInterface $lockValidator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->attrLockValidator = $lockValidator;
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
     * Processing object before save data
     *
     * @return \Magento\Framework\Model\AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        try {
            $this->attrLockValidator->validate($this);
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            throw new \Magento\Framework\Exception\LocalizedException(__($exception->getMessage()));
        }

        $this->setData('modulePrefix', self::MODULE_NAME);
        return parent::beforeSave();
    }

    /**
     * Processing object after save data
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterSave()
    {
        /**
         * Fix saving attribute in admin
         */
        $this->_eavConfig->clear();
        return parent::afterSave();
    }
}

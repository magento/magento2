<?php
/**
 * EAV attribute metadata service
 *
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
namespace Magento\Customer\Service\V1;

use Magento\Customer\Service\V1\CustomerMetadataServiceInterface;
use Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata;
use Magento\Customer\Service\V1\Dto\Eav\OptionBuilder;

class CustomerMetadataService implements CustomerMetadataServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /** @var array Cache of DTOs - entityType => attributeCode => DTO */
    private $_cache;

    /**
     * @var \Magento\Customer\Model\Resource\Form\Attribute\Collection
     */
    private $_attrFormCollection;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    private $_storeManager;

    /**
     * @var \Magento\Customer\Service\V1\Dto\Eav\OptionBuilder
     */
    private $_optionBuilder;

    /**
     * @var \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadataBuilder
     */
    private $_attributeMetadataBuilder;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Resource\Form\Attribute\Collection $attrFormCollection
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Customer\Service\V1\Dto\Eav\OptionBuilder $optionBuilder
     * @param \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadataBuilder $attributeMetadataBuilder
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Resource\Form\Attribute\Collection $attrFormCollection,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Customer\Service\V1\Dto\Eav\OptionBuilder $optionBuilder,
        \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadataBuilder $attributeMetadataBuilder
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_cache = [];
        $this->_attrFormCollection = $attrFormCollection;
        $this->_storeManager = $storeManager;
        $this->_optionBuilder = $optionBuilder;
        $this->_attributeMetadataBuilder = $attributeMetadataBuilder;
    }

    /**
     * Retrieve EAV attribute metadata
     *
     * @param   mixed $entityType
     * @param   mixed $attributeCode
     * @return \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata
     */
    public function getAttributeMetadata($entityType, $attributeCode)
    {
        $dtoCache = $this->_getEntityCache($entityType);
        if (isset($dtoCache[$attributeCode])) {
            return $dtoCache[$attributeCode];
        }

        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */
        $attribute = $this->_eavConfig->getAttribute($entityType, $attributeCode);
        $attributeMetadata = $this->_createMetadataAttribute($attribute);
        $dtoCache[$attributeCode] = $attributeMetadata;
        return $attributeMetadata;
    }

    /**
     * Returns all known attributes metadata for a given entity type and attribute set
     *
     * @param string $entityType
     * @param int $attributeSetId
     * @param int $storeId
     * @return AttributeMetadata[]
     */
    public function getAllAttributeSetMetadata($entityType, $attributeSetId = 0, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $object = new \Magento\Object([
            'store_id'          => $storeId,
            'attribute_set_id'  => $attributeSetId,
        ]);
        $attributeCodes = $this->_eavConfig->getEntityAttributeCodes($entityType, $object);

        $attributesMetadata = [];
        foreach ($attributeCodes as $attributeCode) {
            $attributesMetadata[] = $this->getAttributeMetadata($entityType, $attributeCode);
        }
        return $attributesMetadata;
    }

    /**
     * Retrieve all attributes for entityType filtered by form code
     *
     * @param $entityType
     * @param $formCode
     * @return \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata[]
     */
    public function getAttributes($entityType, $formCode)
    {
        $attributes = [];
        $this->_loadAttributesCollection($entityType, $formCode);
        foreach ($this->_attrFormCollection as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $this->_createMetadataAttribute($attribute);
        }
        return $attributes;
    }

    /**
     * Load collection with filters applied
     *
     * @param $entityType
     * @param $formCode
     * @return null
     */
    private function _loadAttributesCollection($entityType, $formCode)
    {
         $this->_attrFormCollection
            ->setStore($this->_storeManager->getStore())
            ->setEntityType($entityType)
            ->addFormCodeFilter($formCode)
            ->setSortOrder()
            ->load();
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return AttributeMetadata
     */
    private function _createMetadataAttribute($attribute)
    {
        $options = [];
        try {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                $options[$option['label']] = $this->_optionBuilder->setLabel($option['label'])
                    ->setValue($option['value'])
                    ->create();
            }
        } catch (\Exception $e) {
            // There is no source for this attribute
        }
        $this->_attributeMetadataBuilder->setAttributeCode($attribute->getAttributeCode())
            ->setFrontendInput($attribute->getFrontendInput())
            ->setInputFilter($attribute->getInputFilter())
            ->setStoreLabel($attribute->getStoreLabel())
            ->setValidationRules($attribute->getValidateRules())
            ->setIsVisible($attribute->getIsVisible())
            ->setIsRequired($attribute->getIsRequired())
            ->setMultilineCount($attribute->getMultilineCount())
            ->setDataModel($attribute->getDataModel())
            ->setOptions($options);

        return $this->_attributeMetadataBuilder->create();
    }

    /**
     * @inheritdoc
     */
    public function getCustomerAttributeMetadata($attributeCode)
    {
        return $this->getAttributeMetadata('customer', $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getAllCustomerAttributeMetadata()
    {
        return $this->getAllAttributeSetMetadata('customer', self::CUSTOMER_ATTRIBUTE_SET_ID);
    }

    /**
     * @inheritdoc
     */
    public function getAddressAttributeMetadata($attributeCode)
    {
        return $this->getAttributeMetadata('customer_address', $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getAllAddressAttributeMetadata()
    {
        return $this->getAllAttributeSetMetadata('customer_address', self::ADDRESS_ATTRIBUTE_SET_ID);
    }


    /**
     * Helper for getting access to an entity types DTO cache.
     *
     * @param $entityType
     * @return \ArrayAccess
     */
    private function _getEntityCache($entityType)
    {
        if (!isset($this->_cache[$entityType])) {
            $this->_cache[$entityType] = new \ArrayObject();
        }
        return $this->_cache[$entityType];
    }
}

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
namespace Magento\Customer\Service\V1;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Exception\NoSuchEntityException;

/**
 * EAV attribute metadata service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerMetadataService implements CustomerMetadataServiceInterface
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @var \Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory
     */
    private $_attrFormCollectionFactory;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    private $_storeManager;

    /**
     * @var Data\Eav\OptionBuilder
     */
    private $_optionBuilder;

    /**
     * @var Data\Eav\ValidationRuleBuilder
     */
    private $_validationRuleBuilder;

    /**
     * @var Data\Eav\AttributeMetadataBuilder
     */
    private $_attributeMetadataBuilder;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory $attrFormCollectionFactory
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param Data\Eav\OptionBuilder $optionBuilder
     * @param Data\Eav\ValidationRuleBuilder $validationRuleBuilder
     * @param Data\Eav\AttributeMetadataBuilder $attributeMetadataBuilder
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Resource\Form\Attribute\CollectionFactory $attrFormCollectionFactory,
        \Magento\Core\Model\StoreManager $storeManager,
        Data\Eav\OptionBuilder $optionBuilder,
        Data\Eav\ValidationRuleBuilder $validationRuleBuilder,
        Data\Eav\AttributeMetadataBuilder $attributeMetadataBuilder
    ) {
        $this->_eavConfig = $eavConfig;
        $this->_attrFormCollectionFactory = $attrFormCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_optionBuilder = $optionBuilder;
        $this->_validationRuleBuilder = $validationRuleBuilder;
        $this->_attributeMetadataBuilder = $attributeMetadataBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeMetadata($entityType, $attributeCode)
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->_eavConfig->getAttribute($entityType, $attributeCode);
        if ($attribute) {
            $attributeMetadata = $this->_createMetadataAttribute($attribute);
            return $attributeMetadata;
        } else {
            throw (new NoSuchEntityException('entityType', $entityType))->addField('attributeCode', $attributeCode);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributeSetMetadata($entityType, $attributeSetId = 0, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->_storeManager->getStore()->getId();
        }
        $object = new \Magento\Object(array('store_id' => $storeId, 'attribute_set_id' => $attributeSetId));
        $attributeCodes = $this->_eavConfig->getEntityAttributeCodes($entityType, $object);

        $attributesMetadata = array();
        foreach ($attributeCodes as $attributeCode) {
            try {
                $attributesMetadata[] = $this->getAttributeMetadata($entityType, $attributeCode);
            } catch (NoSuchEntityException $e) {
                //If no such entity, skip
            }
        }
        return $attributesMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($entityType, $formCode)
    {
        $attributes = array();
        $attributesFormCollection = $this->_loadAttributesCollection($entityType, $formCode);
        foreach ($attributesFormCollection as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $this->_createMetadataAttribute($attribute);
        }
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerAttributeMetadata($attributeCode)
    {
        return $this->getAttributeMetadata(self::ENTITY_TYPE_CUSTOMER, $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getAllCustomerAttributeMetadata()
    {
        return $this->getAllAttributeSetMetadata(self::ENTITY_TYPE_CUSTOMER, self::ATTRIBUTE_SET_ID_CUSTOMER);
    }

    /**
     * @inheritdoc
     */
    public function getAddressAttributeMetadata($attributeCode)
    {
        return $this->getAttributeMetadata(self::ENTITY_TYPE_ADDRESS, $attributeCode);
    }

    /**
     * @inheritdoc
     */
    public function getAllAddressAttributeMetadata()
    {
        return $this->getAllAttributeSetMetadata(self::ENTITY_TYPE_ADDRESS, self::ATTRIBUTE_SET_ID_ADDRESS);
    }

    /**
     * Load collection with filters applied
     *
     * @param string $entityType
     * @param string $formCode
     * @return \Magento\Customer\Model\Resource\Form\Attribute\Collection
     */
    private function _loadAttributesCollection($entityType, $formCode)
    {
        $attributesFormCollection = $this->_attrFormCollectionFactory->create();
        $attributesFormCollection->setStore(
            $this->_storeManager->getStore()
        )->setEntityType(
            $entityType
        )->addFormCodeFilter(
            $formCode
        )->setSortOrder();

        return $attributesFormCollection;
    }

    /**
     * @param \Magento\Customer\Model\Attribute $attribute
     * @return Data\Eav\AttributeMetadata
     */
    private function _createMetadataAttribute($attribute)
    {
        $options = array();
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $option) {
                $options[$option['label']] = $this->_optionBuilder->setLabel(
                    $option['label']
                )->setValue(
                    $option['value']
                )->create();
            }
        }
        $validationRules = array();
        foreach ($attribute->getValidateRules() as $name => $value) {
            $validationRules[$name] = $this->_validationRuleBuilder->setName($name)->setValue($value)->create();
        }

        $this->_attributeMetadataBuilder->setAttributeCode(
            $attribute->getAttributeCode()
        )->setFrontendInput(
            $attribute->getFrontendInput()
        )->setInputFilter(
            $attribute->getInputFilter()
        )->setStoreLabel(
            $attribute->getStoreLabel()
        )->setValidationRules(
            $validationRules
        )->setVisible(
            $attribute->getIsVisible()
        )->setRequired(
            $attribute->getIsRequired()
        )->setMultilineCount(
            $attribute->getMultilineCount()
        )->setDataModel(
            $attribute->getDataModel()
        )->setOptions(
            $options
        )->setFrontendClass(
            $attribute->getFrontend()->getClass()
        )->setFrontendLabel(
            $attribute->getFrontendLabel()
        )->setNote(
            $attribute->getNote()
        )->setIsSystem(
            $attribute->getIsSystem()
        )->setIsUserDefined(
            $attribute->getIsUserDefined()
        )->setSortOrder(
            $attribute->getSortOrder()
        );

        return $this->_attributeMetadataBuilder->create();
    }

    /**
     * @inheritdoc
     */
    public function getCustomCustomerAttributeMetadata()
    {
        $customAttributes = array();
        foreach ($this->getAllCustomerAttributeMetadata() as $attributeMetadata) {
            if (!$attributeMetadata->isSystem() ||
                $attributeMetadata->getAttributeCode() == 'disable_auto_group_change'
            ) {
                $customAttributes[] = $attributeMetadata;
            }
        }
        return $customAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getCustomAddressAttributeMetadata()
    {
        $customAttributes = array();
        foreach ($this->getAllAddressAttributeMetadata() as $attributeMetadata) {
            if (!$attributeMetadata->isSystem()) {
                $customAttributes[] = $attributeMetadata;
            }
        }
        return $customAttributes;
    }
}

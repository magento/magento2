<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures\AttributeSet;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Persitor for Attribute Sets and Attributes based on the configuration.
 * @since 2.2.0
 */
class AttributeSetFixture
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     * @since 2.2.0
     */
    private $attributeRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeManagementInterface
     * @since 2.2.0
     */
    private $attributeManagement;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory
     * @since 2.2.0
     */
    private $attributeFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     * @since 2.2.0
     */
    private $optionFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSetInterfaceFactory
     * @since 2.2.0
     */
    private $attributeSetFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory
     * @since 2.2.0
     */
    private $attributeGroupFactory;

    /**
     * @var \Magento\Catalog\Api\AttributeSetManagementInterface
     * @since 2.2.0
     */
    private $attributeSetManagement;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface
     * @since 2.2.0
     */
    private $attributeGroupRepository;

    /**
     * AttributeSetsFixture constructor.
     *
     * @param \Magento\Catalog\Api\AttributeSetManagementInterface $attributeSetManagement
     * @param \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface $attributeGroupRepository
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Api\ProductAttributeManagementInterface $attributeManagement
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory $attributeFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
     * @param \Magento\Eav\Api\Data\AttributeSetInterfaceFactory $attributeSetFactory
     * @param \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory $attributeGroupFactory
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Catalog\Api\AttributeSetManagementInterface $attributeSetManagement,
        \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface $attributeGroupRepository,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Api\ProductAttributeManagementInterface $attributeManagement,
        \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory $attributeFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Magento\Eav\Api\Data\AttributeSetInterfaceFactory $attributeSetFactory,
        \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory $attributeGroupFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeManagement = $attributeManagement;
        $this->attributeFactory = $attributeFactory;
        $this->optionFactory = $optionFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    /**
     * Create Attribute Set based on raw data.
     *
     * @param array $attributeSetData
     * @param int $sortOrder
     * @return array
     * @since 2.2.0
     */
    public function createAttributeSet(array $attributeSetData, $sortOrder = 3)
    {
        /** @var \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setAttributeSetName($attributeSetData['name']);
        $attributeSet->setEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);

        try {
            $attributeSet = $this->attributeSetManagement->create($attributeSet, 4);
        } catch (\Exception $e) {
            return $this->getFormattedAttributeSetData($attributeSetData);
        }
        $attributeSetId = $attributeSet->getAttributeSetId();

        /** @var \Magento\Eav\Api\Data\AttributeGroupInterface $attributeGroup */
        $attributeGroup = $this->attributeGroupFactory->create();
        $attributeGroup->setAttributeGroupName($attributeSet->getAttributeSetName() . ' - Group');
        $attributeGroup->setAttributeSetId($attributeSetId);
        $this->attributeGroupRepository->save($attributeGroup);
        $attributeGroupId = $attributeGroup->getAttributeGroupId();

        $attributesData = array_key_exists(0, $attributeSetData['attributes']['attribute'])
            ? $attributeSetData['attributes']['attribute'] : [$attributeSetData['attributes']['attribute']];
        foreach ($attributesData as $attributeData) {
            //Create Attribute
            $optionsData = array_key_exists(0, $attributeData['options']['option'])
                ? $attributeData['options']['option'] : [$attributeData['options']['option']];
            $options = [];
            foreach ($optionsData as $optionData) {
                $option = $this->optionFactory->create(['data' => $optionData]);
                $options[] = $option;
            }

            /** @var  ProductAttributeInterface $attribute */
            $attribute = $this->attributeFactory->create(['data' => $attributeData]);
            $attribute->setOptions($options);
            $attribute->setNote('auto');

            $productAttribute = $this->attributeRepository->save($attribute);
            $attributeId = $productAttribute->getAttributeId();

            //Associate Attribute to Attribute Set
            $this->attributeManagement->assign($attributeSetId, $attributeGroupId, $attributeId, $sortOrder);
        }

        return $this->getFormattedAttributeSetData($attributeSetData);
    }

    /**
     * Return formatted attribute set data
     *
     * @param array $attributeSetData
     * @return array
     * @since 2.2.0
     */
    private function getFormattedAttributeSetData($attributeSetData)
    {
        $attributesData = array_key_exists(0, $attributeSetData['attributes']['attribute'])
            ? $attributeSetData['attributes']['attribute'] : [$attributeSetData['attributes']['attribute']];
        $attributes = [];
        foreach ($attributesData as $attributeData) {
            $optionsData = array_key_exists(0, $attributeData['options']['option'])
                ? $attributeData['options']['option'] : [$attributeData['options']['option']];
            $optionsData = array_map(function ($option) {
                return $option['label'];
            }, $optionsData);
            $attributes[] = [
                'name' => $attributeData['attribute_code'],
                'values' => $optionsData
            ];
        }

        return [
            'name' => $attributeSetData['name'],
            'attributes' => $attributes
        ];
    }
}

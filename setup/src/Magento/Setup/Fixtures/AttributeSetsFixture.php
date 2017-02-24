<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

/**
 * Class AttributeSetFixture
 */
class AttributeSetsFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 25;

    /**
     * Cache for attribute IDs.
     *
     * @var array
     */
    private $attributeIdsCache = [];

    /**
     * Quantity of unique attributes to generate. Zero means infinity.
     *
     * @var int
     */
    protected $uniqueAttributesQuantity = 0;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->populateUniqueAttributesQuantity();
        $attributeSets = $this->getAttributeSetsFixtureValue();
        if ($attributeSets === null) {
            return;
        }
        $this->fixtureModel->resetObjectManager();
        /** @var \Magento\Catalog\Api\AttributeSetManagementInterface $attributeSetManagement */
        $attributeSetManagement = $this->fixtureModel->getObjectManager()->create(
            \Magento\Catalog\Api\AttributeSetManagementInterface::class
        );
        /** @var \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface $attributeGroupRepository */
        $attributeGroupRepository = $this->fixtureModel->getObjectManager()->create(
            \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface::class
        );

        foreach ($attributeSets['attribute_set'] as $attributeSetData) {
            //Create Attribute Set
            /** @var \Magento\Eav\Api\Data\AttributeSetInterfaceFactory $attributeSetFactory */
            $attributeSetFactory = $this->fixtureModel->getObjectManager()->create(
                \Magento\Eav\Api\Data\AttributeSetInterfaceFactory::class
            );
            $attributeSet = $attributeSetFactory->create();
            $attributeSet->setAttributeSetName($attributeSetData['name']);
            $attributeSet->setEntityTypeId(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE);

            $result = $attributeSetManagement->create($attributeSet, 4);
            $attributeSetId = $result->getAttributeSetId();

            //Create Attribute Group
            /** @var \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory $attributeGroupFactory */
            $attributeGroupFactory = $this->fixtureModel->getObjectManager()->create(
                \Magento\Eav\Api\Data\AttributeGroupInterfaceFactory::class
            );
            $attributeGroup = $attributeGroupFactory->create();
            $attributeGroup->setAttributeGroupName($result->getAttributeSetName() . ' - Group');
            $attributeGroup->setAttributeSetId($attributeSetId);
            $attributeGroupRepository->save($attributeGroup);
            $attributeGroupId = $attributeGroup->getAttributeGroupId();

            /** @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository */
            $attributeRepository = $this->fixtureModel->getObjectManager()->create(
                \Magento\Catalog\Api\ProductAttributeRepositoryInterface::class
            );
            /** @var \Magento\Catalog\Api\ProductAttributeManagementInterface $attributeManagementManagement */
            $attributeManagement = $this->fixtureModel->getObjectManager()->create(
                \Magento\Catalog\Api\ProductAttributeManagementInterface::class
            );

            $attributesData = array_key_exists(0, $attributeSetData['attributes']['attribute'])
                ? $attributeSetData['attributes']['attribute'] : [$attributeSetData['attributes']['attribute']];
            foreach ($attributesData as $attributeData) {
                if ($this->uniqueAttributesQuantity === 0
                    || (count($this->attributeIdsCache) < $this->uniqueAttributesQuantity)) {
                    //Create Attribute
                    /** @var  \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory $attributeFactory */
                    $attributeFactory = $this->fixtureModel->getObjectManager()->create(
                        \Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory::class
                    );

                    $optionsData = array_key_exists(0, $attributeData['options']['option'])
                        ? $attributeData['options']['option'] : [$attributeData['options']['option']];
                    $options = [];
                    foreach ($optionsData as $optionData) {
                        /** @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory */
                        $optionFactory = $this->fixtureModel->getObjectManager()->create(
                            \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory::class
                        );
                        $option = $optionFactory->create(['data' => $optionData]);
                        $options[] = $option;
                    }

                    $attribute = $attributeFactory->create(['data' => $attributeData]);
                    $attribute->setOptions($options);

                    $result = $attributeRepository->save($attribute);
                    $attributeId = $result->getAttributeId();
                    $this->fillAttributeIdsCache($attributeId);
                } else {
                    $attributeId = $this->getAttributeIdFromCache();
                }
                //Associate Attribute to Attribute Set
                $sortOrder = 3;

                $attributeManagement->assign($attributeSetId, $attributeGroupId, $attributeId, $sortOrder);
            }
        }
    }

    /**
     * Get attribute ID from cache.
     *
     * @return int
     */
    private function getAttributeIdFromCache()
    {
        $attributeId = next($this->attributeIdsCache);
        if ($attributeId === false) {
            $attributeId = reset($this->attributeIdsCache);
        }

        return $attributeId;
    }

    /**
     * Fill attribute IDs cache.
     *
     * @param int $attributeId
     * @return void
     */
    private function fillAttributeIdsCache($attributeId)
    {
        if ($this->uniqueAttributesQuantity !== 0) {
            $this->attributeIdsCache[] = $attributeId;
        }
    }

    /**
     * Populate quantity of unique attributes to generate.
     *
     * @return void
     */
    protected function populateUniqueAttributesQuantity()
    {
        $this->uniqueAttributesQuantity = $this->fixtureModel->getValue('unique_attributes_quantity', 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating attribute sets';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'attribute_sets' => 'Attribute Sets'
        ];
    }

    /**
     * Get attribute sets fixture value.
     *
     * @return array|null
     */
    protected function getAttributeSetsFixtureValue()
    {
        return $this->fixtureModel->getValue('attribute_sets', null);
    }
}

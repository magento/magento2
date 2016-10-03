<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * {@inheritdoc}
     */
    public function execute()
    {
        $attributeSets = $this->fixtureModel->getValue('attribute_sets', null);
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

                //Associate Attribute to Attribute Set
                $sortOrder = 3;
                $attributeManagement->assign($attributeSetId, $attributeGroupId, $attributeId, $sortOrder);
            }
        }
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
}

<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class AttributeManagement implements \Magento\Eav\Api\AttributeManagementInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $setRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $entityTypeFactory;

    /**
     * @var \Magento\Eav\Api\AttributeGroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $attributeResource;

    /**
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $setRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection
     * @param Config $eavConfig
     * @param ConfigFactory $entityTypeFactory
     * @param \Magento\Eav\Api\AttributeGroupRepositoryInterface $groupRepository
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $attributeResource
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Eav\Api\AttributeSetRepositoryInterface $setRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ConfigFactory $entityTypeFactory,
        \Magento\Eav\Api\AttributeGroupRepositoryInterface $groupRepository,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $attributeResource
    ) {
        $this->setRepository = $setRepository;
        $this->attributeCollection = $attributeCollection;
        $this->eavConfig = $eavConfig;
        $this->entityTypeFactory = $entityTypeFactory;
        $this->groupRepository = $groupRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributeResource = $attributeResource;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($entityTypeCode, $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder)
    {
        try {
            $attributeSet = $this->setRepository->get($attributeSetId);
        } catch (NoSuchEntityException $ex) {
            throw new NoSuchEntityException(__('AttributeSet with id "%1" does not exist.', $attributeSetId));
        }

        $setEntityType = $this->entityTypeFactory->create()->getEntityType($attributeSet->getEntityTypeId());
        if ($setEntityType->getEntityTypeCode() != $entityTypeCode) {
            throw new InputException(__('Wrong attribute set id provided'));
        }

        //Check if group exists. If not - expected exception
        $attributeGroup = $this->groupRepository->get($attributeGroupId);

        if ($attributeGroup->getAttributeSetId() != $attributeSetId) {
            throw new InputException(__('Attribute group does not belong to attribute set'));
        }

        /** @var \Magento\Eav\Api\Data\AttributeInterface $attribute */
        $attribute = $this->attributeRepository->get($entityTypeCode, $attributeCode);

        $this->attributeResource->saveInSetIncluding(
            $attribute,
            $attribute->getAttributeId(),
            $attributeSetId,
            $attributeGroupId,
            $sortOrder
        );
        $attribute->setAttributeSetId($attributeSetId);
        return $attribute->loadEntityAttributeIdBySet()->getData('entity_attribute_id');
    }

    /**
     * {@inheritdoc}
     */
    public function unassign($attributeSetId, $attributeCode)
    {
        try {
            $attributeSet = $this->setRepository->get($attributeSetId);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Attribute set not found: %1', $attributeSetId));
        }
        $setEntityType = $this->entityTypeFactory->create()->getEntityType($attributeSet->getEntityTypeId());

        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        $attribute = $this->attributeRepository->get($setEntityType->getEntityTypeCode(), $attributeCode);

        // Check if attribute is in set
        $attribute->setAttributeSetId($attributeSet->getAttributeSetId());
        $attribute->loadEntityAttributeIdBySet();

        if (!$attribute->getEntityAttributeId()) {
            throw new InputException(
                __('Attribute "%1" not found in attribute set %2.', $attributeCode, $attributeSetId)
            );
        }
        if (!$attribute->getIsUserDefined()) {
            throw new StateException(__('System attribute can not be deleted'));
        }
        $attribute->deleteEntity();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($entityType, $attributeSetId)
    {
        /** @var \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet */
        $attributeSet = $this->setRepository->get($attributeSetId);
        $requiredEntityTypeId = $this->eavConfig->getEntityType($entityType)->getId();
        if (!$attributeSet->getAttributeSetId() || $attributeSet->getEntityTypeId() != $requiredEntityTypeId) {
            throw NoSuchEntityException::singleField('attributeSetId', $attributeSetId);
        }

        $attributeCollection = $this->attributeCollection
            ->setAttributeSetFilter($attributeSet->getAttributeSetId())
            ->load();

        return $attributeCollection->getItems();
    }
}

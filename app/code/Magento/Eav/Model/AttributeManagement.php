<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeManagement implements \Magento\Eav\Api\AttributeManagementInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $setRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     * @deprecated 101.0.0 please use instead \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     * @see $attributeCollectionFactory
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
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $setRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection
     * @param Config $eavConfig
     * @param ConfigFactory $entityTypeFactory
     * @param \Magento\Eav\Api\AttributeGroupRepositoryInterface $groupRepository
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $attributeResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory|null $attributeCollectionFactory
     */
    public function __construct(
        \Magento\Eav\Api\AttributeSetRepositoryInterface $setRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $attributeCollection,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ConfigFactory $entityTypeFactory,
        \Magento\Eav\Api\AttributeGroupRepositoryInterface $groupRepository,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $attributeResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory = null
    ) {
        $this->setRepository = $setRepository;
        $this->attributeCollection = $attributeCollection;
        $this->eavConfig = $eavConfig;
        $this->entityTypeFactory = $entityTypeFactory;
        $this->groupRepository = $groupRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributeResource = $attributeResource;
        $this->attributeCollectionFactory = $attributeCollectionFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory::class);
    }

    /**
     * {@inheritdoc}
     */
    public function assign($entityTypeCode, $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder)
    {
        try {
            $attributeSet = $this->setRepository->get($attributeSetId);
        } catch (NoSuchEntityException $ex) {
            throw new NoSuchEntityException(
                __(
                    'The AttributeSet with a "%1" ID doesn\'t exist. Verify the attributeSet and try again.',
                    $attributeSetId
                )
            );
        }

        $setEntityType = $this->entityTypeFactory->create()->getEntityType($attributeSet->getEntityTypeId());
        if ($setEntityType->getEntityTypeCode() != $entityTypeCode) {
            throw new InputException(__('The attribute set ID is incorrect. Verify the ID and try again.'));
        }

        //Check if group exists. If not - expected exception
        $attributeGroup = $this->groupRepository->get($attributeGroupId);

        if ($attributeGroup->getAttributeSetId() != $attributeSetId) {
            throw new InputException(__('The attribute group doesn\'t belong to the attribute set.'));
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
            throw new NoSuchEntityException(
                __('The "%1" attribute set wasn\'t found. Verify and try again.', $attributeSetId)
            );
        }
        $setEntityType = $this->entityTypeFactory->create()->getEntityType($attributeSet->getEntityTypeId());

        /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
        $attribute = $this->attributeRepository->get($setEntityType->getEntityTypeCode(), $attributeCode);

        // Check if attribute is in set
        $attribute->setAttributeSetId($attributeSet->getAttributeSetId());
        $attribute->loadEntityAttributeIdBySet();

        if (!$attribute->getEntityAttributeId()) {
            throw new InputException(
                __(
                    'The "%1" attribute wasn\'t found in the "%2" attribute set. Enter the attribute and try again.',
                    $attributeCode,
                    $attributeSetId
                )
            );
        }
        if (!$attribute->getIsUserDefined()) {
            throw new StateException(__("The system attribute can't be deleted."));
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
        $attributeCollection = $this->attributeCollectionFactory->create();
        $attributeCollection->setAttributeSetFilter($attributeSet->getAttributeSetId())->load();

        return $attributeCollection->getItems();
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Class \Magento\Catalog\Model\ProductAttributeGroupRepository
 *
 * @since 2.0.0
 */
class ProductAttributeGroupRepository implements \Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeGroupRepositoryInterface
     * @since 2.0.0
     */
    protected $groupRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\GroupFactory
     * @since 2.0.0
     */
    protected $groupFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group
     * @since 2.0.0
     */
    protected $groupResource;

    /**
     * @param \Magento\Eav\Api\AttributeGroupRepositoryInterface $groupRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group $groupResource
     * @param Product\Attribute\GroupFactory $groupFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Eav\Api\AttributeGroupRepositoryInterface $groupRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group $groupResource,
        \Magento\Catalog\Model\Product\Attribute\GroupFactory $groupFactory
    ) {
        $this->groupRepository = $groupRepository;
        $this->groupResource = $groupResource;
        $this->groupFactory = $groupFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function save(\Magento\Eav\Api\Data\AttributeGroupInterface $group)
    {
        return $this->groupRepository->save($group);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->groupRepository->getList($searchCriteria);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($groupId)
    {
        /** @var \Magento\Catalog\Model\Product\Attribute\Group $group */
        $group = $this->groupFactory->create();
        $this->groupResource->load($group, $groupId);
        if (!$group->getId()) {
            throw new NoSuchEntityException(__('Group with id "%1" does not exist.', $groupId));
        }
        return $group;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function deleteById($groupId)
    {
        $this->delete(
            $this->get($groupId)
        );
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function delete(\Magento\Eav\Api\Data\AttributeGroupInterface $group)
    {
        /** @var \Magento\Catalog\Model\Product\Attribute\Group $group */
        if ($group->hasSystemAttributes()) {
            throw new StateException(
                __('Attribute group that contains system attributes can not be deleted')
            );
        }
        return $this->groupRepository->delete($group);
    }
}

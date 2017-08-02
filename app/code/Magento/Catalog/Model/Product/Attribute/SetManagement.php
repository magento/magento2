<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Framework\Exception\StateException;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\SetManagement
 *
 * @since 2.0.0
 */
class SetManagement implements \Magento\Catalog\Api\AttributeSetManagementInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeSetManagementInterface
     * @since 2.0.0
     */
    protected $attributeSetManagement;

    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     * @since 2.0.0
     */
    protected $attributeSetRepository;

    /**
     * @var \Magento\Eav\Model\Config
     * @since 2.0.0
     */
    protected $eavConfig;

    /**
     * @param \Magento\Eav\Api\AttributeSetManagementInterface $attributeSetManagement
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param \Magento\Eav\Model\Config $eavConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Eav\Api\AttributeSetManagementInterface $attributeSetManagement,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->attributeSetManagement = $attributeSetManagement;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function create(\Magento\Eav\Api\Data\AttributeSetInterface $attributeSet, $skeletonId)
    {
        $this->validateSkeletonSet($skeletonId);
        return $this->attributeSetManagement->create(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeSet,
            $skeletonId
        );
    }

    /**
     * @param int $skeletonId
     * @return void
     * @throws StateException
     * @since 2.0.0
     */
    protected function validateSkeletonSet($skeletonId)
    {
        try {
            $skeletonSet = $this->attributeSetRepository->get($skeletonId);
            $productEntityId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
            if ($skeletonSet->getEntityTypeId() != $productEntityId) {
                throw new StateException(
                    __('Can not create attribute set based on non product attribute set.')
                );
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            throw new StateException(
                __('Can not create attribute set based on not existing attribute set')
            );
        }
    }
}

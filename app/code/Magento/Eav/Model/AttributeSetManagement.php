<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Eav\Api\AttributeSetManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\InputException;

class AttributeSetManagement implements AttributeSetManagementInterface
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $repository;

    /**
     * @param Config $eavConfig
     * @param AttributeSetRepositoryInterface $repository
     */
    public function __construct(
        EavConfig $eavConfig,
        AttributeSetRepositoryInterface $repository
    ) {
        $this->eavConfig = $eavConfig;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function create($entityTypeCode, AttributeSetInterface $attributeSet, $skeletonId)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        if ($attributeSet->getId() !== null) {
            throw InputException::invalidFieldValue('id', $attributeSet->getId());
        }
        if ($skeletonId == 0) {
            throw InputException::invalidFieldValue('skeletonId', $skeletonId);
        }
        // Make sure that skeleton attribute set is valid (try to load it)
        $this->repository->get($skeletonId);

        try {
            $attributeSet->setEntityTypeId($this->eavConfig->getEntityType($entityTypeCode)->getId());
            $attributeSet->validate();
        } catch (\Exception $exception) {
            throw new InputException($exception->getMessage());
        }

        $this->repository->save($attributeSet);
        $attributeSet->initFromSkeleton($skeletonId);

        return $this->repository->save($attributeSet);
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;

class AttributeRepository implements CategoryAttributeRepositoryInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $eavAttributeRepository;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->eavAttributeRepository = $eavAttributeRepository;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        return $this->eavAttributeRepository->getList(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get($attributeCode)
    {
        return $this->eavAttributeRepository->get(
            \Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCustomAttributesMetadata($dataObjectClassName = null)
    {
        $defaultAttributeSetId = $this->eavConfig
            ->getEntityType(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE)
            ->getDefaultAttributeSetId();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('attribute_set_id')
                    ->setValue($defaultAttributeSetId)
                    ->create(),
            ]
        );

        return $this->getList($searchCriteria->create())->getItems();
    }
}

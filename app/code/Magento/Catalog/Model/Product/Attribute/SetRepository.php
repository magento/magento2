<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Framework\Exception\InputException;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\SetRepository
 *
 */
class SetRepository implements \Magento\Catalog\Api\AttributeSetRepositoryInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Magento\Eav\Api\Data\AttributeSetInterface $attributeSet)
    {
        $this->validate($attributeSet);
        return $this->attributeSetRepository->save($attributeSet);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('entity_type_code')
                    ->setValue(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
                    ->setConditionType('eq')
                    ->create(),
            ]
        );
        $this->searchCriteriaBuilder->setCurrentPage($searchCriteria->getCurrentPage());
        $this->searchCriteriaBuilder->setPageSize($searchCriteria->getPageSize());
        return $this->attributeSetRepository->getList($this->searchCriteriaBuilder->create());
    }

    /**
     * {@inheritdoc}
     */
    public function get($attributeSetId)
    {
        $attributeSet = $this->attributeSetRepository->get($attributeSetId);
        $this->validate($attributeSet);
        return $attributeSet;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Magento\Eav\Api\Data\AttributeSetInterface $attributeSet)
    {
        $this->validate($attributeSet);
        return $this->attributeSetRepository->delete($attributeSet);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($attributeSetId)
    {
        $this->get($attributeSetId);
        return $this->attributeSetRepository->deleteById($attributeSetId);
    }

    /**
     * Validate Frontend Input Type
     *
     * @param  \Magento\Eav\Api\Data\AttributeSetInterface $attributeSet
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function validate(\Magento\Eav\Api\Data\AttributeSetInterface $attributeSet)
    {
        $productEntityId = $this->eavConfig->getEntityType(\Magento\Catalog\Model\Product::ENTITY)->getId();
        if ($attributeSet->getEntityTypeId() != $productEntityId) {
            throw new \Magento\Framework\Exception\StateException(
                __('Provided Attribute set non product Attribute set.')
            );
        }
    }
}

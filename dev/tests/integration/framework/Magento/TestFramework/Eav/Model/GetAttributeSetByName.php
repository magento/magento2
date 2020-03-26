<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Search and return attribute set by name.
 */
class GetAttributeSetByName
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * Find attribute set by name and return it.
     *
     * @param string $attributeSetName
     * @return AttributeSetInterface|null
     */
    public function execute(string $attributeSetName): ?AttributeSetInterface
    {
        $this->searchCriteriaBuilder->addFilter('attribute_set_name', $attributeSetName);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $result = $this->attributeSetRepository->getList($searchCriteria);
        $items = $result->getItems();

        return array_pop($items);
    }
}

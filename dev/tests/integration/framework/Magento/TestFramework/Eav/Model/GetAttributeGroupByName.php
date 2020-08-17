<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Eav\Model;

use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Search and return attribute group by name.
 */
class GetAttributeGroupByName
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AttributeGroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeGroupRepositoryInterface $attributeGroupRepository
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeGroupRepositoryInterface $attributeGroupRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->groupRepository = $attributeGroupRepository;
    }

    /**
     * Returns attribute group by name.
     *
     * @param int $setId
     * @param string $groupName
     * @return AttributeGroupInterface|null
     */
    public function execute(int $setId, string $groupName): ?AttributeGroupInterface
    {
        $searchCriteria =  $this->searchCriteriaBuilder->addFilter(
            AttributeGroupInterface::GROUP_NAME,
            $groupName
        )->addFilter(
            AttributeGroupInterface::ATTRIBUTE_SET_ID,
            $setId
        )->create();
        $result = $this->groupRepository->getList($searchCriteria)->getItems();

        return array_shift($result);
    }
}

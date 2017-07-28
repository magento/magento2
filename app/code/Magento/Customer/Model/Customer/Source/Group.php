<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Customer\Source;

use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class \Magento\Customer\Model\Customer\Source\Group
 *
 * @since 2.1.0
 */
class Group implements GroupSourceInterface
{
    /**
     * @var ModuleManager
     * @since 2.1.0
     */
    protected $moduleManager;

    /**
     * @var GroupRepositoryInterface
     * @since 2.1.0
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     * @since 2.1.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @param ModuleManager $moduleManager
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @since 2.1.0
     */
    public function __construct(
        ModuleManager $moduleManager,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->moduleManager = $moduleManager;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Return array of customer groups
     *
     * @return array
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        if (!$this->moduleManager->isEnabled('Magento_Customer')) {
            return [];
        }
        $customerGroups = [];
        $customerGroups[] = [
            'label' => __('ALL GROUPS'),
            'value' => GroupInterface::CUST_GROUP_ALL,
        ];

        /** @var GroupSearchResultsInterface $groups */
        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create());
        foreach ($groups->getItems() as $group) {
            $customerGroups[] = [
                'label' => $group->getCode(),
                'value' => $group->getId(),
            ];
        }

        return $customerGroups;
    }
}

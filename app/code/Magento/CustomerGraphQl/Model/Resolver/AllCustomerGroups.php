<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Exception;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Config\AccountInformation;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Provides data for allCustomerGroups.name
 */
class AllCustomerGroups implements ResolverInterface
{
    /**
     * AllCustomerGroups Constructor
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AccountInformation $config
     */
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly SearchCriteriaBuilder    $searchCriteriaBuilder,
        private readonly AccountInformation       $config
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        if (!$this->config->isShareAllCustomerGroupsEnabled()) {
            throw new GraphQlInputException(__('Sharing customer group information is disabled or not configured.'));
        }

        try {
            $customerGroups = $this->groupRepository->getList(
                $this->searchCriteriaBuilder->create()
            )->getItems();
        } catch (Exception $e) {
            throw new GraphQlInputException(__('Unable to retrieve customer groups.'));
        }

        return array_map(
            fn ($group) => ['name' => $group->getCode()],
            array_filter(
                $customerGroups,
                fn ($group) => !in_array(
                    (int)$context->getExtensionAttributes()->getStore()->getWebsiteId(),
                    $group->getExtensionAttributes()->getExcludeWebsiteIds() ?? []
                )
            )
        );
    }
}

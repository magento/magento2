<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CatalogCustomerGraphQl\Model\Resolver\Customer\GetCustomerGroup as CustomerGroup;
use Magento\Customer\Model\Config\AccountInformation;
use Magento\CustomerGraphQl\Model\GetCustomerGroupName;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Provides data for customerGroup.name
 */
class GetCustomerGroup implements ResolverInterface
{
    /**
     * GetCustomerGroup Constructor
     *
     * @param AccountInformation $config
     * @param CustomerGroup $customerGroup
     * @param GetCustomerGroupName $getCustomerGroup
     */
    public function __construct(
        private readonly AccountInformation   $config,
        private readonly CustomerGroup        $customerGroup,
        private readonly GetCustomerGroupName $getCustomerGroup
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
        if (!$this->config->isShareCustomerGroupEnabled()) {
            throw new GraphQlInputException(__('Sharing customer group information is disabled or not configured.'));
        }

        return $this->getCustomerGroup->execute(
            $this->customerGroup->execute($context->getUserId()),
            (int)$context->getExtensionAttributes()->getStore()->getWebsiteId()
        );
    }
}

<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\AccountInformation;
use Magento\CustomerGraphQl\Model\GetCustomerGroupName;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Provides data for customer.group.name
 */
class CustomerGroup implements ResolverInterface
{
    /**
     * CustomerGroup Constructor
     *
     * @param AccountInformation $config
     * @param GetCustomerGroupName $getCustomerGroup
     */
    public function __construct(
        private readonly AccountInformation   $config,
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
    ): ?array {
        if (!($value['model'] ?? null) instanceof CustomerInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        return !$this->config->isShareCustomerGroupEnabled() ? null :
            $this->getCustomerGroup->execute(
                (int) $value['model']->getGroupId(),
                (int) $context->getExtensionAttributes()->getStore()->getWebsiteId()
            );
    }
}

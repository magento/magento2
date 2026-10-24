<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CatalogCustomerGraphQl\Model\Resolver\Customer\GetCustomerGroup;
use Magento\Customer\Model\Config\AccountInformation;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Visitor implements ResolverInterface
{
    /**
     * Visitor Constructor
     *
     * @param AccountInformation $config
     * @param GetCustomerGroup $getCustomerGroup
     * @param Uid $idEncoder
     */
    public function __construct(
        private readonly AccountInformation $config,
        private readonly GetCustomerGroup   $getCustomerGroup,
        private readonly Uid                $idEncoder
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

        return [
            'uid' => $this->idEncoder->encode((string)$this->getCustomerGroup->execute($context->getUserId()))
        ];
    }
}

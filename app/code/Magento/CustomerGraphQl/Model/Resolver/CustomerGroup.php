<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Model\Config\AccountInformation;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CustomerGroup implements ResolverInterface
{
    /**
     * CustomerGroup Constructor
     *
     * @param AccountInformation $config
     * @param Uid $idEncoder
     */
    public function __construct(
        private readonly AccountInformation $config,
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
    ): ?array {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!$this->config->isShareCustomerGroupEnabled()) {
            return null;
        }

        return [
            'uid' => $this->idEncoder->encode((string)$value['model']->getGroupId())
        ];
    }
}

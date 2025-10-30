<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CustomerAddressUid implements ResolverInterface
{
    /**
     * CustomerAddressUid Constructor
     *
     * @param Uid $idEncoder
     */
    public function __construct(
        private readonly Uid $idEncoder
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
    ): string {
        if (!isset($value['id'])) {
            throw new LocalizedException(__('Missing required address ID.'));
        }

        return $this->idEncoder->encode((string) $value['id']);
    }
}

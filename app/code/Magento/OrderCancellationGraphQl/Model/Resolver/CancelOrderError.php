<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver to return the description of a CancellationReason with error code
 */
class CancelOrderError implements ResolverInterface
{
    /**
     * @param array $errorMessageCodesMapper
     */
    public function __construct(
        private readonly array $errorMessageCodesMapper
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): ?array {
        if (empty($value['error'])) {
            return null;
        }

        return [
            'message' => $value['error'],
            'code' => $this->errorMessageCodesMapper[strtolower((string) $value['error'])] ?? 'UNDEFINED',
        ];
    }
}

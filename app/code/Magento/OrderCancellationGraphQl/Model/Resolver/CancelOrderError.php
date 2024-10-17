<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained from
 * Adobe.
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
        array $value = null,
        array $args = null
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

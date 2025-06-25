<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver for generating CancellationReasons
 */
class CancellationReasons implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!is_array($value['order_cancellation_reasons'])) {
            $cancellationReasons = json_decode($value['order_cancellation_reasons'], true);
        } else {
            $cancellationReasons = $value['order_cancellation_reasons'];
        }

        return $cancellationReasons;
    }
}

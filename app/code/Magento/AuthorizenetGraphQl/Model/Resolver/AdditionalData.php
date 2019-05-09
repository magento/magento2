<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQL\DataObjectConverter;

/**
 * @inheritdoc
 */
class AdditionalData implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['additional_data'])) {
            return [];
        }
        $additionalData = [];
        foreach ($value['additional_data'] as $key => $value) {
            $additionalData[DataObjectConverter::camelCaseToSnakeCase($key)] = $value;
        }
        return $additionalData;
    }
}

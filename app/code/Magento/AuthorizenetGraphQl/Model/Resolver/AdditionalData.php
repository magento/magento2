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
use Magento\AuthorizenetAcceptjs\Gateway\Config;

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
        $authorizeNet = $value[Config::METHOD];
        if (!isset($authorizeNet)) {
            return [];
        }
        $additionalData = [];
        foreach ($authorizeNet as $key => $value) {
            $additionalData[DataObjectConverter::camelCaseToSnakeCase($key)] = $value;
            unset($additionalData[$key]);
        }
        return $additionalData;
    }
}

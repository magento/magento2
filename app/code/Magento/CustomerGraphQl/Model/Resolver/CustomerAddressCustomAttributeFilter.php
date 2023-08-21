<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver Customer Address Custom Attribute filter
 */
class CustomerAddressCustomAttributeFilter implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        $customAttributes = $value[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES . 'V2'];
        if (isset($args['attributeCodes']) && !empty($args['attributeCodes'])) {
            $attributeCodes = array_values($args['attributeCodes']);
            return array_filter($customAttributes, function ($attr) use ($attributeCodes) {
                return in_array($attr['code'], $attributeCodes);
            });
        }

        return $customAttributes;
    }
}

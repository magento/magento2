<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Product\Fields;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * @inheritdoc
 */
class DynamicPrice implements ResolverInterface
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
    ) {
        $result = null;
        if ($value['type_id'] === Bundle::TYPE_CODE) {
            $result = isset($value['price_type']) ? !$value['price_type'] : null;
        }

        return $result;
    }
}

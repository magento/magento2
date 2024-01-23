<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Variant;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver class for product variant.
 */
class Variant implements ResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (isset($value['variant']['model']) && $value['variant']['model']) {
            return
                array_merge(
                    $value['variant']['model']->getData(),
                    [
                        'model' => $value['variant']['model']
                    ]
                );
        } else {
            return null;
        }
    }
}

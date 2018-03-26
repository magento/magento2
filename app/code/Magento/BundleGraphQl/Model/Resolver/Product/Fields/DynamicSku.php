<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\BundleGraphQl\Model\Resolver\Product\Fields;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;

/**
 * {@inheritdoc}
 */
class DynamicSku implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     */
    public function __construct(ValueFactory $valueFactory)
    {
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ): ?Value {
        $result = null;
        if ($value['type_id'] === Bundle::TYPE_CODE) {
            $result = isset($value['sku_type']) ? !$value['sku_type'] : null;
        }

        return $this->valueFactory->create(
            function () use ($result) {
                return $result;
            }
        );
    }
}

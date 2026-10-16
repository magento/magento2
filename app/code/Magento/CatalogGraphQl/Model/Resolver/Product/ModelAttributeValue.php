<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolves a dynamic product attribute whose code collides with the "model" key of the product source array
 */
class ModelAttributeValue implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $attributeCode = $field->getName();
        $model = $value['model'] ?? null;

        if ($model instanceof DataObject) {
            return $model->getData($attributeCode);
        }

        return $value[$attributeCode] ?? null;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;

/**
 * Class Product
 */
class Product implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * {@inheritDoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ): ?Value {
        if (!isset($value['model'])) {
            return null;
        }

        $result = function () use ($value) {
            /** @var \Magento\Catalog\Model\Product $productModel */
            $productModel = $value['model'];
            $productData = $productModel->getData();

            if (!empty($productModel->getCustomAttributes())) {
                foreach ($productModel->getCustomAttributes() as $customAttribute) {
                    if (!isset($productData[$customAttribute->getAttributeCode()])) {
                        $productData[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
                    }
                }
            }

            $data = array_replace($value, $productData);
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}

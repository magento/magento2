<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CatalogGraphQl\Model\Product\Attributes\Collection as AttributesCollection;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;

/**
 * Retrieves CustomAttributes
 */
class CustomAttributes implements ResolverInterface
{
    /**
     * @var AttributesCollection
     */
    private $attributesCollection;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param AttributesCollection $attributesCollection
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        AttributesCollection $attributesCollection,
        ValueFactory $valueFactory
    ) {
        $this->attributesCollection = $attributesCollection;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $product = $value['model'];

        $this->attributesCollection->addProductId((int)$product->getId());
        $productId = $product->getId();
        $result = function () use ($productId) {
            return $this->attributesCollection->getAttributesValueByProductId((int)$productId);
        };
        return $this->valueFactory->create($result);
    }
}

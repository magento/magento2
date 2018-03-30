<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Options;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product;

/**
 * Class Label
 */
class Label implements ResolverInterface
{

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var Product
     */
    private $product;

    /**
     * @param ValueFactory $valueFactory
     * @param Product $product
     */
    public function __construct(ValueFactory $valueFactory, Product $product)
    {
        $this->valueFactory = $valueFactory;
        $this->product = $product;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ): ?Value {
        if (!isset($value['sku'])) {
            return null;
        }

        $this->product->addProductSku($value['sku']);
        $this->product->addEavAttributes(['name']);

        $result = function () use ($value) {
            $productData = $this->product->getProductBySku($value['sku']);
            /** @var \Magento\Catalog\Model\Product $productModel */
            $productModel = isset($productData['model']) ? $productData['model'] : null;
            return $productModel ? $productModel->getName() : null;
        };

        return $this->valueFactory->create($result);
    }

}

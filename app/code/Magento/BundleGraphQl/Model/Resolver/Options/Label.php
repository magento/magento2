<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Options;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

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
     * @var ProductDataProvider
     */
    private $product;

    /**
     * @param ValueFactory $valueFactory
     * @param ProductDataProvider $product
     */
    public function __construct(ValueFactory $valueFactory, ProductDataProvider $product)
    {
        $this->valueFactory = $valueFactory;
        $this->product = $product;
    }

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
        if (!isset($value['sku'])) {
            throw new LocalizedException(__('"sku" value should be specified'));
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

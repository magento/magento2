<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\ConfigurableProductGraphQl\Model\Product\Collection as ProductDataProvider;

/**
 * Fetches the Product data according to the GraphQL schema
 */
class ProductResolver implements ResolverInterface
{
    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ProductDataProvider $productDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        ProductDataProvider $productDataProvider,
        ValueFactory $valueFactory   
    )
    {
        $this->productDataProvider = $productDataProvider;
        $this->valueFactory = $valueFactory;    }

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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $cartItem = $value['model'];
        $sku = $cartItem->getSku();
        $this->productDataProvider->addProductSku($sku);
        $result = function () use ($sku) {
            return $this->productDataProvider->getProductBySku($sku);
        };
        return $this->valueFactory->create($result);
    }
}
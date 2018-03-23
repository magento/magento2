<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\Attributes\Collection;

/**
 * {@inheritdoc}
 */
class ChildProduct implements ResolverInterface
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
     * @var Collection
     */
    private $collection;

    /**
     * @param ProductDataProvider $productDataProvider
     * @param ValueFactory $valueFactory
     * @param Collection $collection
     */
    public function __construct(
        ProductDataProvider $productDataProvider,
        ValueFactory $valueFactory,
        Collection $collection
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->valueFactory = $valueFactory;
        $this->collection = $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        if (!isset($value['sku'])) {
            throw new GraphQlInputException(__('No child sku found for product link.'));
        }
        $this->productDataProvider->addProductSku($value['sku']);
        $fields = $this->getProductFields($info);
        $matchedFields = $this->collection->getRequestAttributes($fields);
        $this->productDataProvider->addEavAttributes($matchedFields);

        $result = function () use ($value) {
            return $this->productDataProvider->getProductBySku($value['sku']);
        };

        return $this->valueFactory->create($result);
    }

    /**
     * Return field names for all requested product fields.
     *
     * @param ResolveInfo $info
     * @return string[]
     */
    private function getProductFields(ResolveInfo $info)
    {
        $fieldNames = [];
        foreach ($info->fieldNodes as $node) {
            $fieldNames[] = $node->name->value;
        }

        return $fieldNames;
    }
}

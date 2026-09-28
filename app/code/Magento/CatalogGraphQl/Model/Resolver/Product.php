<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\ProductFactory as ProductDataProviderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * @inheritdoc
 */
class Product implements ResolverInterface
{
    /**
     * @var ProductDataProvider
     */
    private ProductDataProvider $productDataProvider;

    /**
     * @var ValueFactory
     */
    private ValueFactory $valueFactory;

    /**
     * @var ProductFieldsSelector
     */
    private ProductFieldsSelector $productFieldsSelector;

    /**
     * @param ProductDataProvider $productDataProvider
     * @param ValueFactory $valueFactory
     * @param ProductFieldsSelector $productFieldsSelector
     * @param ProductDataProviderFactory|null $productDataProviderFactory @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ProductDataProvider $productDataProvider,
        ValueFactory $valueFactory,
        ProductFieldsSelector $productFieldsSelector,
        ?ProductDataProviderFactory $productDataProviderFactory = null
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->valueFactory = $valueFactory;
        $this->productFieldsSelector = $productFieldsSelector;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!isset($value['sku'])) {
            throw new GraphQlInputException(__('No child sku found for product link.'));
        }
        $this->productDataProvider->addProductSku($value['sku']);
        $fields = $this->productFieldsSelector->getProductFieldsFromInfo($info);
        $this->productDataProvider->addEavAttributes($fields);
        $result = function () use ($value, $context) {
            $data = $value['product'] ?? $this->productDataProvider->getProductBySku($value['sku'], $context);
            if (empty($data)) {
                return null;
            }
            if (!isset($data['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            $productModel = $data['model'];
            /** @var \Magento\Catalog\Model\Product $productModel */
            $data = $productModel->getData();
            $data['model'] = $productModel;
            if (!empty($productModel->getCustomAttributes())) {
                foreach ($productModel->getCustomAttributes() as $customAttribute) {
                    if (!isset($data[$customAttribute->getAttributeCode()])) {
                        $data[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
                    }
                }
            }
            return array_replace($value, $data);
        };
        return $this->valueFactory->create($result);
    }
}

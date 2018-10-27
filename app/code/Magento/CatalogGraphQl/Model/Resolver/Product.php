<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Deferred\Product as ProductDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\FieldTranslator;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * @inheritdoc
 */
class Product implements ResolverInterface
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
     * @var FieldTranslator
     */
    private $fieldTranslator;

    /**
     * @param ProductDataProvider $productDataProvider
     * @param ValueFactory $valueFactory
     * @param FieldTranslator $fieldTranslator
     */
    public function __construct(
        ProductDataProvider $productDataProvider,
        ValueFactory $valueFactory,
        FieldTranslator $fieldTranslator
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->valueFactory = $valueFactory;
        $this->fieldTranslator = $fieldTranslator;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['sku'])) {
            throw new GraphQlInputException(__('No child sku found for product link.'));
        }
        $this->productDataProvider->addProductSku($value['sku']);
        $fields = $this->getProductFields($info);
        $this->productDataProvider->addEavAttributes($fields);

        $result = function () use ($value) {
            $data = $this->productDataProvider->getProductBySku($value['sku']);
            if (empty($data)) {
                return null;
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

    /**
     * Return field names for all requested product fields.
     *
     * @param ResolveInfo $info
     * @return string[]
     */
    private function getProductFields(ResolveInfo $info) : array
    {
        $fieldNames = [];
        foreach ($info->fieldNodes as $node) {
            if ($node->name->value !== 'product') {
                continue;
            }
            foreach ($node->selectionSet->selections as $selectionNode) {
                if ($selectionNode->kind === 'InlineFragment') {
                    foreach ($selectionNode->selectionSet->selections as $inlineSelection) {
                        if ($inlineSelection->kind === 'InlineFragment') {
                            continue;
                        }
                        $fieldNames[] = $this->fieldTranslator->translate($inlineSelection->name->value);
                    }
                    continue;
                }
                $fieldNames[] = $this->fieldTranslator->translate($selectionNode->name->value);
            }
        }

        return $fieldNames;
    }
}

<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\FilterProductCustomAttribute;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\ProductDataProvider;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\EavGraphQl\Model\Output\Value\GetAttributeValueInterface;
use Magento\EavGraphQl\Model\Resolver\GetFilteredAttributes;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 *
 * Format a product's custom attribute information to conform to GraphQL schema representation
 */
class ProductCustomAttributes implements ResolverInterface
{
    /**
     * @var GetAttributeValueInterface
     */
    private GetAttributeValueInterface $getAttributeValue;

    /**
     * @var ProductDataProvider
     */
    private ProductDataProvider $productDataProvider;

    /**
     * @var GetFilteredAttributes
     */
    private GetFilteredAttributes $getFilteredAttributes;

    /**
     * @var FilterProductCustomAttribute
     */
    private FilterProductCustomAttribute $filterCustomAttribute;

    /**
     * @param GetAttributeValueInterface $getAttributeValue
     * @param ProductDataProvider $productDataProvider
     * @param GetFilteredAttributes $getFilteredAttributes
     * @param FilterProductCustomAttribute $filterCustomAttribute
     */
    public function __construct(
        GetAttributeValueInterface $getAttributeValue,
        ProductDataProvider $productDataProvider,
        GetFilteredAttributes $getFilteredAttributes,
        FilterProductCustomAttribute $filterCustomAttribute
    ) {
        $this->getAttributeValue = $getAttributeValue;
        $this->productDataProvider = $productDataProvider;
        $this->getFilteredAttributes = $getFilteredAttributes;
        $this->filterCustomAttribute = $filterCustomAttribute;
    }

    /**
     * @inheritdoc
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $filtersArgs = $args['filters'] ?? [];

        $productCustomAttributes = $this->getFilteredAttributes->execute(
            $filtersArgs,
            ProductAttributeInterface::ENTITY_TYPE_CODE
        );

        $attributeCodes = array_map(
            function (AttributeInterface $customAttribute) {
                return $customAttribute->getAttributeCode();
            },
            $productCustomAttributes['items']
        );

        $filteredAttributeCodes = $this->filterCustomAttribute->execute(array_flip($attributeCodes));

        /** @var Product $product */
        $product = $value['model'];
        $productData = $this->productDataProvider->getProductDataById((int)$product->getId());

        $customAttributes = [];
        foreach ($filteredAttributeCodes as $attributeCode => $value) {
            if (!array_key_exists($attributeCode, $productData)) {
                continue;
            }
            $attributeValue = $productData[$attributeCode] ?? "";
            if (is_array($attributeValue)) {
                $attributeValue = implode(',', $attributeValue);
            }
            $customAttributes[] = [
                'attribute_code' => $attributeCode,
                'value' => $attributeValue
            ];
        }

        return [
            'items' => array_map(
                function (array $customAttribute) {
                    return $this->getAttributeValue->execute(
                        ProductAttributeInterface::ENTITY_TYPE_CODE,
                        $customAttribute['attribute_code'],
                        $customAttribute['value']
                    );
                },
                $customAttributes
            ),
            'errors' => $productCustomAttributes['errors']
        ];
    }
}

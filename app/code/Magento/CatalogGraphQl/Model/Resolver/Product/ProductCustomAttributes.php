<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\FilterProductCustomAttribute;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\ProductDataProvider;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\EavGraphQl\Model\Output\Value\GetAttributeValueInterface;
use Magento\EavGraphQl\Model\Resolver\AttributeFilter;
use Magento\Framework\Api\SearchCriteriaBuilder;
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
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var GetAttributeValueInterface
     */
    private GetAttributeValueInterface $getAttributeValue;

    /**
     * @var ProductDataProvider
     */
    private ProductDataProvider $productDataProvider;

    /**
     * @var AttributeFilter
     */
    private AttributeFilter $attributeFilter;

    /**
     * @var FilterProductCustomAttribute
     */
    private FilterProductCustomAttribute $filterCustomAttribute;

    /**
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GetAttributeValueInterface $getAttributeValue
     * @param ProductDataProvider $productDataProvider
     * @param AttributeFilter $attributeFilter
     * @param FilterProductCustomAttribute $filterCustomAttribute
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GetAttributeValueInterface $getAttributeValue,
        ProductDataProvider $productDataProvider,
        AttributeFilter $attributeFilter,
        FilterProductCustomAttribute $filterCustomAttribute
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->getAttributeValue = $getAttributeValue;
        $this->productDataProvider = $productDataProvider;
        $this->attributeFilter = $attributeFilter;
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
        array $value = null,
        array $args = null
    ) {
        $filterArgs = $args['filter'] ?? [];

        $searchCriteriaBuilder = $this->attributeFilter->execute($filterArgs, $this->searchCriteriaBuilder);

        $searchCriteriaBuilder = $searchCriteriaBuilder
            ->addFilter('is_visible', true)
            ->addFilter('backend_type', 'static', 'neq')
            ->create();

        $productCustomAttributes = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteriaBuilder
        )->getItems();

        $attributeCodes = array_map(
            function (AttributeInterface $customAttribute) {
                return $customAttribute->getAttributeCode();
            },
            $productCustomAttributes
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
            $attributeValue = $productData[$attributeCode];
            if (is_array($attributeValue)) {
                $attributeValue = implode(',', $attributeValue);
            }
            $customAttributes[] = [
                'attribute_code' => $attributeCode,
                'value' => $attributeValue
            ];
        }

        return array_map(
            function (array $customAttribute) {
                return $this->getAttributeValue->execute(
                    ProductAttributeInterface::ENTITY_TYPE_CODE,
                    $customAttribute['attribute_code'],
                    $customAttribute['value']
                );
            },
            $customAttributes
        );
    }
}

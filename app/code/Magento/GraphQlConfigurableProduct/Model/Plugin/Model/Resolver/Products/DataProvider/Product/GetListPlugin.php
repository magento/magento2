<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlConfigurableProduct\Model\Plugin\Model\Resolver\Products\DataProvider\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection
    as AttributeCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection as ProductCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\GraphQlCatalog\Model\Resolver\Products\DataProvider\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

class GetListPlugin
{
    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * @var ProductCollection
     */
    private $productCollection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * GetListPlugin constructor.
     *
     * @param Configurable $configurable
     * @param AttributeCollection $attributeCollection
     * @param ProductCollection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Configurable $configurable,
        AttributeCollection $attributeCollection,
        ProductCollection $productCollection,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->configurable = $configurable;
        $this->attributeCollection = $attributeCollection;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Intercept GraphQLCatalog getList, and add any necessary configurable fields
     *
     * @param Product $subject
     * @param SearchResultsInterface $result
     * @return SearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(Product $subject, SearchResultsInterface $result)
    {
        $processConfigurableData = false;
        /** @var ProductInterface $product */
        foreach ($result->getItems() as $product) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $this->productCollection->setProductFilter($product);
                $this->attributeCollection->setProductFilter($product);
                $processConfigurableData = true;
            }
        }

        if ($processConfigurableData) {
            /** @var \Magento\Catalog\Model\Product[] $children */
            $children = $this->productCollection->getItems();
            /** @var Attribute[] $attributes */
            $attributes = $this->attributeCollection->getItems();
        }

        foreach ($result->getItems() as $product) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $extensionAttributes = $product->getExtensionAttributes();
                $childrenIds = [];
                foreach ($children as $child) {
                    if ($child->getParentId() === $product->getId()) {
                        $childrenIds[] = $child->getId();
                    }
                }
                $productAttributes = [];
                foreach ($attributes as $attribute) {
                    if ($attribute->getProductId() === $product->getId()) {
                        $productAttributes[] = $attribute;
                    }
                }
                $extensionAttributes->setConfigurableProductLinks($childrenIds);
                $extensionAttributes->setConfigurableProductOptions($productAttributes);
                $product->setExtensionAttributes($extensionAttributes);
            }
        }

        return $result;
    }

    /**
     * Add and format configurable data to product result
     *
     * @param Product $productDataProvider
     * @param array $result
     * @param ProductInterface $product
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcessProduct(Product $productDataProvider, array $result, ProductInterface $product)
    {
        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            /** @var OptionInterface[] $options */
            $options = $product->getExtensionAttributes()->getConfigurableProductOptions();
            foreach ($options as $option) {
                foreach ($result['configurable_product_options'] as $key => $resultOption) {
                    if ($resultOption['id'] === (int)$option->getId()) {
                        $result['configurable_product_options'][$key]['values'] = $option->getOptions();
                    }
                }
            }
        }

        if (isset($result['configurable_product_links'])) {
            $result['configurable_product_links'] = $this
                ->resolveConfigurableProductLinks($result['configurable_product_links'], $productDataProvider);
        }

        return $result;
    }

    /**
     * Resolve links for configurable product into simple products
     *
     * @param int[]
     * @param Product $productDataProvider
     * @return array
     */
    private function resolveConfigurableProductLinks(array $configurableProductLinks, Product $productDataProvider)
    {
        if (empty($configurableProductLinks)) {
            return [];
        }
        $result = [];
        $this->searchCriteriaBuilder->addFilter('entity_id', $configurableProductLinks, 'in');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $childProductResult = $productDataProvider->getList($searchCriteria);
        /** @var ProductInterface $product */
        foreach ($childProductResult->getItems() as $product) {
            $result[$product->getId()] = $productDataProvider->processProduct($product);
        }
        return $result;
    }
}

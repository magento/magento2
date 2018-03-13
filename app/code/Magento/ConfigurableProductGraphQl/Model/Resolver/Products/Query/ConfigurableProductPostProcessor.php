<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Products\Query;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Framework\GraphQl\Query\PostFetchProcessorInterface;

/**
 * Retrieves simple product data for child products and formats configurable data
 */
class ConfigurableProductPostProcessor implements PostFetchProcessorInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Product
     */
    private $productDataProvider;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Product $productDataProvider
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param FormatterInterface $formatter
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Product $productDataProvider,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        FormatterInterface $formatter
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productDataProvider = $productDataProvider;
        $this->productResource = $productResource;
        $this->formatter = $formatter;
    }

    /**
     * Process all configurable product data, including adding simple product data and formatting relevant attributes.
     *
     * @param array $resultData
     * @return array
     */
    public function process(array $resultData)
    {
        $childrenIds = [];
        foreach ($resultData as $key => $product) {
            if (isset($product['type_id']) && $product['type_id'] === Configurable::TYPE_CODE) {
                $formattedChildIds = [];
                if (isset($product['configurable_product_links'])) {
                    foreach ($product['configurable_product_links'] as $childId) {
                        $childrenIds[] = (int)$childId;
                        $formattedChildIds[$childId] = null;
                    }
                }
                $resultData[$key]['configurable_product_links'] = $formattedChildIds;
            }
        }

        $this->searchCriteriaBuilder->addFilter('entity_id', $childrenIds, 'in');
        $childProducts = $this->productDataProvider->getList($this->searchCriteriaBuilder->create());
        $resultData = $this->addChildData($childProducts->getItems(), $resultData);

        return $resultData;
    }

    /**
     * Format and add configurable child data to their matching products result items.
     *
     * @param \Magento\Catalog\Model\Product[] $childProducts
     * @param array $resultData
     * @return array
     */
    private function addChildData(array $childProducts, array $resultData)
    {
        /** @var \Magento\Catalog\Model\Product $childProduct */
        foreach ($childProducts as $childProduct) {
            $childData = $this->formatter->format($childProduct);
            $childId = (int)$childProduct->getId();
            foreach ($resultData as $key => $item) {
                if (isset($item['configurable_product_links'])
                    && array_key_exists($childId, $item['configurable_product_links'])
                ) {
                    $resultData[$key]['configurable_product_links'][$childId] = $childData;
                    $categoryLinks = $this->productResource->getCategoryIds($childProduct);
                    foreach ($categoryLinks as $position => $link) {
                        $resultData[$key]['configurable_product_links'][$childId]['category_links'][] =
                            ['position' => $position, 'category_id' => $link];
                    }
                }
            }
        }
        return $resultData;
    }
}

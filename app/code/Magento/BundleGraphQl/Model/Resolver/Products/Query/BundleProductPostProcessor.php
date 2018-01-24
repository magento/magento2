<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model\Resolver\Products\Query;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;

/**
 * Retrieves simple product data for child products, and formats configurable data
 */
class BundleProductPostProcessor implements \Magento\Framework\GraphQl\Query\PostFetchProcessorInterface
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
     * Process all bundle product data, including adding simple product data and formatting relevant attributes.
     *
     * @param array $resultData
     * @return array
     */
    public function process(array $resultData)
    {
        $childrenIds = [];
        foreach ($resultData as $productKey => $product) {
            if ($product['type_id'] === Bundle::TYPE_CODE) {
                if (isset($product['bundle_product_options'])) {
                    foreach ($product['bundle_product_options'] as $optionKey => $option) {
                        $formattedChildIds = [];
                        foreach ($option['product_links'] as $linkKey => $link) {
                            $childrenIds[] = (int)$link['entity_id'];
                            $formattedChildIds[$link['entity_id']] = null;
                            // reformat entity id to product id
                            $resultData[$productKey]['bundle_product_options'][$optionKey]['values']
                            [$linkKey]['product_id'] = $link['entity_id'];
                        }
                        $resultData[$productKey]['bundle_product_links'] = $formattedChildIds;
                    }
                }
            }
        }

        $this->searchCriteriaBuilder->addFilter('entity_id', $childrenIds, 'in');
        $childProducts = $this->productDataProvider->getList($this->searchCriteriaBuilder->create());
        /** @var \Magento\Catalog\Model\Product $childProduct */
        foreach ($childProducts->getItems() as $childProduct) {
            $childData = $this->formatter->format($childProduct);
            $childId = (int)$childProduct->getId();
            foreach ($resultData as $productKey => $item) {
                if (isset($item['bundle_product_links'])
                    && array_key_exists($childId, $item['bundle_product_links'])
                ) {
                    $resultData[$productKey]['bundle_product_links'][$childId] = $childData;
                    $categoryLinks = $this->productResource->getCategoryIds($childProduct);
                    foreach ($categoryLinks as $position => $link) {
                        $resultData[$productKey]['bundle_product_links'][$childId]['category_links'][] =
                            ['position' => $position, 'category_id' => $link];
                    }
                }
            }
        }

        return $resultData;
    }
}

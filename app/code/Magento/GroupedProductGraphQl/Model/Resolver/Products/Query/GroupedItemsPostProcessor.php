<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProductGraphQl\Model\Resolver\Products\Query;

use Magento\Framework\GraphQl\Query\PostFetchProcessorInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProductGraphQl\Model\Resolver\Products\DataProvider\Product\Formatter\ProductLinks;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Fetch child data and format to grouped product link
 */
class GroupedItemsPostProcessor implements PostFetchProcessorInterface
{
    /**
     * @var Product
     */
    private $productDataProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private $productResource;

    /**
     * @param Product $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FormatterInterface $formatter
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     */
    public function __construct(
        Product $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FormatterInterface $formatter,
        \Magento\Catalog\Model\ResourceModel\Product $productResource
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->formatter = $formatter;
        $this->productResource = $productResource;
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $resultData)
    {
        $childrenSkus = [];
        foreach ($resultData as $product) {
            if ($product['type_id'] === Grouped::TYPE_CODE) {
                if (isset($product['items'])) {
                    foreach ($product['items'] as $link) {
                        if ($link['product']['link_type'] !== ProductLinks::LINK_TYPE) {
                            continue;
                        }
                        $childrenSkus[] = $link['product']['linked_product_sku'];
                    }
                }
            }
        }

        $this->searchCriteriaBuilder->addFilter(ProductInterface::SKU, $childrenSkus, 'in');
        $childResults = $this->productDataProvider->getList($this->searchCriteriaBuilder->create());
        $resultData = $this->addChildData($childResults->getItems(), $resultData);

        return $resultData;
    }

    /**
     * Format and add child data of grouped products to matching grouped items
     *
     * @param \Magento\Catalog\Model\Product[] $childResults
     * @param array $resultData
     * @return array
     */
    private function addChildData(array $childResults, array $resultData)
    {
        foreach ($childResults as $child) {
            $childData = $this->formatter->format($child);
            $childSku = $child->getSku();
            foreach ($resultData as $key => $item) {
                foreach ($item['items'] as $linkKey => $link) {
                    if (!isset($link['product']['linked_product_sku'])
                        || $link['product']['link_type'] !== ProductLinks::LINK_TYPE
                        || $link['product']['linked_product_sku'] !== $childSku
                    ) {
                        continue;
                    }
                    $resultData[$key]['items'][$linkKey]['product'] = $childData;
                    $categoryLinks = $this->productResource->getCategoryIds($child);
                    foreach ($categoryLinks as $position => $catLink) {
                        $resultData[$key]['items'][$linkKey]['product']['category_links'][] =
                            ['position' => $position, 'category_id' => $catLink];
                    }
                }
            }
        }

        return $resultData;
    }
}

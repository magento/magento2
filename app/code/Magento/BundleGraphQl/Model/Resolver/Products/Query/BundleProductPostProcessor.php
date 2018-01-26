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
use Magento\Bundle\Model\Link;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\GraphQl\Query\EnumLookup;

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
     * @var ProductResource
     */
    private $productResource;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Product $productDataProvider
     * @param ProductResource $productResource
     * @param FormatterInterface $formatter
     * @param EnumLookup $enumLookup
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Product $productDataProvider,
        ProductResource $productResource,
        FormatterInterface $formatter,
        EnumLookup $enumLookup
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productDataProvider = $productDataProvider;
        $this->productResource = $productResource;
        $this->formatter = $formatter;
        $this->enumLookup = $enumLookup;
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
                $resultData[$productKey] = $this->formatBundleAttributes($product);
                if (isset($product['bundle_product_options'])) {
                    foreach ($product['bundle_product_options'] as $optionKey => $option) {
                        $formattedChildIds = [];
                        $resultData[$productKey]['bundle_product_options'][$optionKey]
                            = $this->formatProductOptions($option);
                        foreach ($option['product_links'] as $linkKey => $link) {
                            $childrenIds[] = (int)$link['entity_id'];
                            $formattedChildIds[$link['entity_id']] = null;
                            $resultData[$productKey]['bundle_product_options'][$optionKey]['values'][$linkKey]
                                = $this->formatProductOptionLinks($link);
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

    /**
     * Format bundle specific top level attributes from product
     *
     * @param array $product
     * @return array
     */
    private function formatBundleAttributes(array $product)
    {
        $product['price_view']
            = $this->enumLookup->getEnumValueFromField('PriceViewEnum', $product['price_view']);
        $product['ship_bundle_items']
            = $this->enumLookup->getEnumValueFromField('ShipBundleItemsEnum', $product['shipment_type']);
        $product['dynamic_price'] =!(bool)$product['price_type'];
        $product['dynamic_sku'] =!(bool)$product['sku_type'];
        $product['dynamic_weight'] =!(bool)$product['weight_type'];
        return $product;
    }

    /**
     * Format bundle option product links
     *
     * @param Link $link
     * @return array
     */
    private function formatProductOptionLinks(Link $link)
    {
        $returnData = $link->getData();
        $returnData['product_id'] = $link->getEntityId();
        $returnData['can_change_quantity'] = $link->getCanChangeQuantity();
        $returnData['price_type'] = $this->enumLookup->getEnumValueFromField('PriceTypeEnum', $link->getPriceType());
        return $returnData;
    }

    /**
     * Format bundle option
     *
     * @param Option $option
     * @return array
     */
    private function formatProductOptions(Option $option)
    {
        return $option->getData();
    }
}

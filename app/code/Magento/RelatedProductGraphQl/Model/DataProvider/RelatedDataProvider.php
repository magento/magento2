<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RelatedProductGraphQl\Model\DataProvider;

use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\RelatedProductGraphQl\Model\DataProvider\Products\LinkedProductsDataProvider;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Related Products Data Provider
 */
class RelatedDataProvider
{
    /**
     * @var LinkFactory
     */
    private $dataProvider;

    /**
     * @var ProductFieldsSelector
     */
    private $productFieldsSelector;

    /**
     * @var int
     */
    private $linkType;

    /**
     * @var string
     */
    private $schemaNodeName;

    /**
     * @param LinkedProductsDataProvider $dataProvider
     * @param ProductFieldsSelector $productFieldsSelector
     * @param int $linkType
     * @param string $schemaNodeName
     */
    public function __construct(
        LinkedProductsDataProvider $dataProvider,
        ProductFieldsSelector $productFieldsSelector,
        int $linkType = Link::LINK_TYPE_RELATED,
        string $schemaNodeName = 'related_products'
    ) {
        $this->dataProvider = $dataProvider;
        $this->productFieldsSelector = $productFieldsSelector;
        $this->linkType = $linkType;
        $this->schemaNodeName = $schemaNodeName;
    }

    /**
     * Related Products Data
     *
     * @param ResolveInfo $info
     * @param array $value
     * @return array
     */
    public function getProducts(ResolveInfo $info, array $value): array
    {
        $product = $value['model'];
        $fields = $this->productFieldsSelector->getProductFieldsFromInfo($info, $this->schemaNodeName);
        $products = $this->dataProvider->getRelatedProducts($product, $fields, $this->linkType);

        $data = [];
        foreach ($products as $key => $product) {
            $data[$key] = $product->getData();
            $data[$key]['model'] = $product;
        }

        return $data;
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\Catalog\Model\Product;
use Magento\Swatches\Helper\Data;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class ProductSubstitute
{
    /**
     * Determine context of creation image block
     * which defined in catalog/product/list.phtml
     */
    const CATEGORY_PAGE_GRID_LOCATION = 'category_page_grid';
    const CATEGORY_PAGE_LIST_LOCATION = 'category_page_list';

    /**
     * Data helper to get child product image
     *
     * @var Data $productHelper
     */
    private $swatchHelperData;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Http
     */
    private $request;

    /**
     * @param Data $swatchesHelperData
     * @param Config $eavConfig
     * @param Http $request
     */
    public function __construct (Data $swatchesHelperData, Config $eavConfig, Http $request)
    {
        $this->swatchHelperData = $swatchesHelperData;
        $this->eavConfig = $eavConfig;
        $this->request = $request;
    }

    /**
     * Replace original configurable product with first child
     *
     * @param Product $product
     * @param string $location
     * @param array $attributes
     * @return array
     */
    public function replace(Product $product, $location, array $attributes = [])
    {
        if ($product->getTypeId() == Configurable::TYPE_CODE
            && ($location == self::CATEGORY_PAGE_GRID_LOCATION || $location == self::CATEGORY_PAGE_LIST_LOCATION)) {
            $request = $this->request->getParams();
            if (is_array($request)) {
                $filterArray = $this->getFilterArray($request);
                if (!empty($filterArray)) {
                    $product = $this->loadSimpleVariation($product, $filterArray);
                }
            }
        }
        return [$product, $location, $attributes];
    }

    /**
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @param array $filterArray
     * @return bool|\Magento\Catalog\Model\Product
     */
    public function loadSimpleVariation(\Magento\Catalog\Model\Product $parentProduct, array $filterArray)
    {
        $childProduct = $this->swatchHelperData->loadVariationByFallback($parentProduct, $filterArray);
        if ($childProduct && !$childProduct->getImage()) {
            $childProduct = $this->swatchHelperData->loadFirstVariationWithImage($parentProduct, $filterArray);
        }
        if (!$childProduct) {
            $childProduct = $parentProduct;
        }
        return $childProduct;
    }

    /**
     * Get filters from request
     *
     * @param array $request
     * @return array
     */
    public function getFilterArray(array $request)
    {
        $filterArray = [];
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(\Magento\Catalog\Model\Product::ENTITY);
        foreach ($request as $code => $value) {
            if (in_array($code, $attributeCodes)) {
                $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $code);
                if ($attribute->getId() && $this->canReplaceImageWithSwatch($attribute)) {
                    $filterArray[$code] = $value;
                }
            }
        }
        return $filterArray;
    }

    /**
     * Check if we can replace original image with swatch image on catalog/category/list page
     *
     * @param Attribute $attribute
     * @return bool
     */
    public function canReplaceImageWithSwatch($attribute)
    {
        $result = true;
        if (!$this->swatchHelperData->isSwatchAttribute($attribute)) {
            $result = false;
        }

        if (!$attribute->getUsedInProductListing()
            || !$attribute->getIsFilterable()
            || !$attribute->getData('update_product_preview_image')
        ) {
            $result = false;
        }

        return $result;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Swatches\Helper\Data;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class ProductImage replace original configurable product with first child
 */
class ProductImage
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
    protected $swatchHelperData;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @param Data $swatchesHelperData
     * @param Config $eavConfig
     * @param Http $request
     */
    public function __construct(
        Data $swatchesHelperData,
        Config $eavConfig,
        Http $request
    ) {
        $this->swatchHelperData = $swatchesHelperData;
        $this->eavConfig = $eavConfig;
        $this->request = $request;
    }

    /**
     * Replace original configurable product with first child
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param AbstractProduct $subject
     * @param ProductModel $product
     * @param string $location
     * @param array $attributes
     * @return array
     */
    public function beforeGetImage(
        AbstractProduct $subject,
        ProductModel $product,
        $location,
        array $attributes = []
    ) {
        if ($product->getTypeId() == Configurable::TYPE_CODE
            && ($location == self::CATEGORY_PAGE_GRID_LOCATION || $location == self::CATEGORY_PAGE_LIST_LOCATION)) {
            $request = $this->request->getParams();
            if (is_array($request)) {
                $filterArray = $this->getFilterArray($request, $product);
                if (!empty($filterArray)) {
                    $product = $this->loadSimpleVariation($product, $filterArray);
                }
            }
        }
        return [$product, $location, $attributes];
    }

    /**
     * @param Product $parentProduct
     * @param array $filterArray
     * @return bool|Product
     */
    private function loadSimpleVariation(Product $parentProduct, array $filterArray)
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
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function getFilterArray(array $request, Product $product)
    {
        $filterArray = [];
        $attributes = $this->eavConfig->getEntityAttributes(Product::ENTITY, $product);
        foreach ($request as $code => $value) {
            if (isset($attributes[$code])) {
                $attribute = $attributes[$code];
                if ($this->canReplaceImageWithSwatch($attribute)) {
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
    private function canReplaceImageWithSwatch($attribute)
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

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Swatches\Model\ProductSubstitute;
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
     * @var ProductSubstitute
     */
    private $productSubstitute;

    /**
     * @param Data $swatchesHelperData
     * @param Config $eavConfig
     * @param Http $request
     * @param ProductSubstitute|null $productSubstitute
     */
    public function __construct (
        Data $swatchesHelperData,
        Config $eavConfig,
        Http $request,
        ProductSubstitute $productSubstitute = null
    ) {
        $this->swatchHelperData = $swatchesHelperData;
        $this->eavConfig = $eavConfig;
        $this->request = $request;
        $this->productSubstitute = $productSubstitute ?: ObjectManager::getInstance()->get(ProductSubstitute::class);
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
        return $this->productSubstitute->replace($product, $location, $attributes);
    }

    /**
     * @param ProductModel $parentProduct
     * @param array $filterArray
     * @return bool|Product
     * @deprecated
     * @see ProductSubstitute::loadSimpleVariation()
     */
    protected function loadSimpleVariation(ProductModel $parentProduct, array $filterArray)
    {
        return $this->productSubstitute->loadSimpleVariation($parentProduct, $filterArray);
    }

    /**
     * Get filters from request
     *
     * @param array $request
     * @return array
     * @deprecated
     * @see ProductSubstitute::getFilterArray()
     */
    protected function getFilterArray(array $request)
    {
        return $this->productSubstitute->getFilterArray($request);
    }

    /**
     * Check if we can replace original image with swatch image on catalog/category/list page
     *
     * @param Attribute $attribute
     * @return bool
     * @deprecated
     * @see ProductSubstitute::canReplaceImageWithSwatch()
     */
    protected function canReplaceImageWithSwatch($attribute)
    {
        return $this->productSubstitute->canReplaceImageWithSwatch($attribute);
    }
}

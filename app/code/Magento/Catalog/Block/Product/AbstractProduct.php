<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Class AbstractProduct
 * @api
 * @deprecated 2.2.0
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class AbstractProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_priceBlock = [];

    /**
     * Flag which allow/disallow to use link for as low as price
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_useLinkForAsLowAs = true;

    /**
     * Default product amount per row
     *
     * @var int
     * @since 2.0.0
     */
    protected $_defaultColumnCount = 3;

    /**
     * Product amount per row depending on custom page layout of category
     *
     * @var array
     * @since 2.0.0
     */
    protected $_columnCountLayoutDepend = [];

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $_taxData;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     * @since 2.0.0
     */
    protected $_catalogConfig;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $_mathRandom;

    /**
     * @var \Magento\Checkout\Helper\Cart
     * @since 2.0.0
     */
    protected $_cartHelper;

    /**
     * @var \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    protected $_wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     * @since 2.0.0
     */
    protected $_compareProduct;

    /**
     * @var \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    protected $_imageHelper;

    /**
     * @var ReviewRendererInterface
     * @since 2.0.0
     */
    protected $reviewRenderer;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     * @since 2.0.0
     */
    protected $stockRegistry;

    /**
     * @var ImageBuilder
     * @since 2.2.0
     */
    protected $imageBuilder;

    /**
     * @param Context $context
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Block\Product\Context $context, array $data = [])
    {
        $this->_imageHelper = $context->getImageHelper();
        $this->imageBuilder = $context->getImageBuilder();
        $this->_compareProduct = $context->getCompareProduct();
        $this->_wishlistHelper = $context->getWishlistHelper();
        $this->_cartHelper = $context->getCartHelper();
        $this->_catalogConfig = $context->getCatalogConfig();
        $this->_coreRegistry = $context->getRegistry();
        $this->_taxData = $context->getTaxData();
        $this->_mathRandom = $context->getMathRandom();
        $this->reviewRenderer = $context->getReviewRenderer();
        $this->stockRegistry = $context->getStockRegistry();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     * @since 2.0.0
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieves url for form submitting.
     *
     * Some objects can use setSubmitRouteData() to set route and params for form submitting,
     * otherwise default url will be used
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     * @since 2.0.0
     */
    public function getSubmitUrl($product, $additional = [])
    {
        $submitRouteData = $this->getData('submit_route_data');
        if ($submitRouteData) {
            $route = $submitRouteData['route'];
            $params = isset($submitRouteData['params']) ? $submitRouteData['params'] : [];
            $submitUrl = $this->getUrl($route, array_merge($params, $additional));
        } else {
            $submitUrl = $this->getAddToCartUrl($product, $additional);
        }
        return $submitUrl;
    }

    /**
     * Retrieve add to wishlist params
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @since 2.0.0
     */
    public function getAddToWishlistParams($product)
    {
        return $this->_wishlistHelper->getAddParams($product);
    }

    /**
     * Retrieve Add Product to Compare Products List URL
     *
     * @return string
     * @since 2.0.0
     */
    public function getAddToCompareUrl()
    {
        return $this->_compareProduct->getAddUrl();
    }

    /**
     * Gets minimal sales quantity
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int|null
     * @since 2.0.0
     */
    public function getMinimalQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minSaleQty = $stockItem->getMinSaleQty();
        return $minSaleQty > 0 ? $minSaleQty : null;
    }

    /**
     * Get product reviews summary
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     * @return string
     * @since 2.0.0
     */
    public function getReviewsSummaryHtml(
        \Magento\Catalog\Model\Product $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        return $this->reviewRenderer->getReviewsSummaryHtml($product, $templateType, $displayIfNoReviews);
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @since 2.0.0
     */
    protected function _addProductAttributesAndPrices(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addUrlRewrite();
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     * @since 2.0.0
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @since 2.0.0
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve product amount per row
     *
     * @return int
     * @since 2.0.0
     */
    public function getColumnCount()
    {
        if (!$this->_getData('column_count')) {
            $pageLayout = $this->getPageLayout();
            if ($pageLayout && $this->getColumnCountLayoutDepend($pageLayout->getCode())) {
                $this->setData('column_count', $this->getColumnCountLayoutDepend($pageLayout->getCode()));
            } else {
                $this->setData('column_count', $this->_defaultColumnCount);
            }
        }
        return (int) $this->_getData('column_count');
    }

    /**
     * Add row size depends on page layout
     *
     * @param string $pageLayout
     * @param int $columnCount
     * @return \Magento\Catalog\Block\Product\ListProduct
     * @since 2.0.0
     */
    public function addColumnCountLayoutDepend($pageLayout, $columnCount)
    {
        $this->_columnCountLayoutDepend[$pageLayout] = $columnCount;
        return $this;
    }

    /**
     * Remove row size depends on page layout
     *
     * @param string $pageLayout
     * @return \Magento\Catalog\Block\Product\ListProduct
     * @since 2.0.0
     */
    public function removeColumnCountLayoutDepend($pageLayout)
    {
        if (isset($this->_columnCountLayoutDepend[$pageLayout])) {
            unset($this->_columnCountLayoutDepend[$pageLayout]);
        }

        return $this;
    }

    /**
     * Retrieve row size depends on page layout
     *
     * @param string $pageLayout
     * @return int|boolean
     * @since 2.0.0
     */
    public function getColumnCountLayoutDepend($pageLayout)
    {
        if (isset($this->_columnCountLayoutDepend[$pageLayout])) {
            return $this->_columnCountLayoutDepend[$pageLayout];
        }

        return false;
    }

    /**
     * Retrieve current page layout
     *
     * @return string
     * @since 2.0.0
     */
    public function getPageLayout()
    {
        // TODO: Implement of getting  current page layout
        return '';
    }

    /**
     * Check whether the price can be shown for the specified product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getCanShowProductPrice($product)
    {
        return $product->getCanShowPrice() !== false;
    }

    /**
     * Get if it is necessary to show product stock status
     *
     * @return bool
     * @since 2.0.0
     */
    public function displayProductStockStatus()
    {
        $statusInfo = new \Magento\Framework\DataObject(['display_status' => true]);
        $this->_eventManager->dispatch('catalog_block_product_status_display', ['status' => $statusInfo]);
        return (bool) $statusInfo->getDisplayStatus();
    }

    /**
     * Get random string
     *
     * @param int $length
     * @param string|null $chars
     * @return string
     * @since 2.0.0
     */
    public function getRandomString($length, $chars = null)
    {
        return $this->_mathRandom->getRandomString($length, $chars);
    }

    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @since 2.0.0
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        return $this->getProductPriceHtml(
            $product,
            \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
            \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
        );
    }

    /**
     * Return HTML block with tier price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @since 2.0.0
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render($priceType, $product, $arguments);
        }
        return $price;
    }

    /**
     * Whether redirect to cart enabled
     *
     * @return bool
     * @since 2.0.0
     */
    public function isRedirectToCartEnabled()
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve product details html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     * @since 2.0.0
     */
    public function getProductDetailsHtml(\Magento\Catalog\Model\Product $product)
    {
        $renderer = $this->getDetailsRenderer($product->getTypeId());
        if ($renderer) {
            $renderer->setProduct($product);
            return $renderer->toHtml();
        }
        return '';
    }

    /**
     * @param null $type
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    public function getDetailsRenderer($type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getDetailsRendererList();
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }

    /**
     * @return \Magento\Framework\View\Element\RendererList
     * @since 2.0.0
     */
    protected function getDetailsRendererList()
    {
        return $this->getDetailsRendererListName() ? $this->getLayout()->getBlock(
            $this->getDetailsRendererListName()
        ) : $this->getChildBlock(
            'details.renderers'
        );
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     * @since 2.0.0
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }
}

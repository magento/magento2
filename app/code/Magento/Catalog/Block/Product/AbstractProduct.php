<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Abstract Block
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Product;

use Magento\View\Element\BlockInterface;

abstract class AbstractProduct extends \Magento\View\Element\Template
{
    /**
     * @var array
     */
    protected $_priceBlock = array();

    /**
     * Default price block
     *
     * @var string
     */
    protected $_block = 'Magento\Catalog\Block\Product\Price';

    /**
     * @var string
     */
    protected $_priceBlockDefaultTemplate = 'product/price.phtml';

    /**
     * @var string
     */
    protected $_tierPriceDefaultTemplate = 'product/view/tierprices.phtml';

    /**
     * @var array
     */
    protected $_priceBlockTypes = array();

    /**
     * Flag which allow/disallow to use link for as low as price
     *
     * @var bool
     */
    protected $_useLinkForAsLowAs = true;

    /**
     * Default product amount per row
     *
     * @var int
     */
    protected $_defaultColumnCount = 3;

    /**
     * Product amount per row depending on custom page layout of category
     *
     * @var array
     */
    protected $_columnCountLayoutDepend = array();

    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp';

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxData = null;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * @var \Magento\Math\Random
     */
    protected $_mathRandom;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $_compareProduct;

    /**
     * @var \Magento\Theme\Helper\Layout
     */
    protected $_layoutHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var ReviewRendererInterface
     */
    protected $reviewRenderer;

    /**
     * @param Context $context
     * @param array $data
     * @param array $priceBlockTypes
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        array $data = array(),
        array $priceBlockTypes = array()
    ) {
        $this->_imageHelper = $context->getImageHelper();
        $this->_layoutHelper = $context->getLayoutHelper();
        $this->_compareProduct = $context->getCompareProduct();
        $this->_wishlistHelper = $context->getWishlistHelper();
        $this->_cartHelper = $context->getCartHelper();
        $this->_catalogConfig = $context->getCatalogConfig();
        $this->_coreRegistry = $context->getRegistry();
        $this->_taxData = $context->getTaxData();
        $this->_catalogData = $context->getCatalogHelper();
        $this->_mathRandom = $context->getMathRandom();
        $this->_priceBlockTypes = $priceBlockTypes;
        $this->reviewRenderer = $context->getReviewRenderer();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieves url for form submitting:
     * some objects can use setSubmitRouteData() to set route and params for form submitting,
     * otherwise default url will be used
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getSubmitUrl($product, $additional = array())
    {
        $submitRouteData = $this->getData('submit_route_data');
        if ($submitRouteData) {
            $route = $submitRouteData['route'];
            $params = isset($submitRouteData['params']) ? $submitRouteData['params'] : array();
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
     */
    public function getAddToWishlistParams($product)
    {
        return $this->_wishlistHelper->getAddParams($product);
    }

    /**
     * Retrieve Add Product to Compare Products List URL
     *
     * @return string
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
     */
    public function getMinimalQty($product)
    {
        $stockItem = $product->getStockItem();
        if ($stockItem) {
            return $stockItem->getMinSaleQty() &&
                $stockItem->getMinSaleQty() > 0 ? $stockItem->getMinSaleQty() * 1 : null;
        }
        return null;
    }

    /**
     * @param string $productTypeId
     * @return BlockInterface
     */
    protected function _getPriceBlock($productTypeId)
    {
        if (!isset($this->_priceBlock[$productTypeId])) {
            $block = $this->_block;
            if (isset($this->_priceBlockTypes[$productTypeId])) {
                if ($this->_priceBlockTypes[$productTypeId]['block'] != '') {
                    $block = $this->_priceBlockTypes[$productTypeId]['block'];
                }
            }
            $this->_priceBlock[$productTypeId] = $this->getLayout()->createBlock($block);
        }
        return $this->_priceBlock[$productTypeId];
    }

    /**
     * @param string $productTypeId
     * @return string
     */
    protected function _getPriceBlockTemplate($productTypeId)
    {
        if (isset($this->_priceBlockTypes[$productTypeId])) {
            if ($this->_priceBlockTypes[$productTypeId]['template'] != '') {
                return $this->_priceBlockTypes[$productTypeId]['template'];
            }
        }
        return $this->_priceBlockDefaultTemplate;
    }

    /**
     * Prepares and returns block to render some product type
     *
     * @param string $productType
     * @return \Magento\View\Element\Template
     */
    public function _preparePriceRenderer($productType)
    {
        return $this->_getPriceBlock(
            $productType
        )->setTemplate(
            $this->_getPriceBlockTemplate($productType)
        )->setUseLinkForAsLowAs(
            $this->_useLinkForAsLowAs
        );
    }

    /**
     * Returns product price block html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param boolean $displayMinimalPrice
     * @param string $idSuffix
     * @return string
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $type_id = $product->getTypeId();
        if ($this->_catalogData->canApplyMsrp($product)) {
            $realPriceHtml = $this->_preparePriceRenderer(
                $type_id
            )->setProduct(
                $product
            )->setDisplayMinimalPrice(
                $displayMinimalPrice
            )->setIdSuffix(
                $idSuffix
            )->toHtml();
            $product->setAddToCartUrl($this->getAddToCartUrl($product));
            $product->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }

        return $this->_preparePriceRenderer(
            $type_id
        )->setProduct(
            $product
        )->setDisplayMinimalPrice(
            $displayMinimalPrice
        )->setIdSuffix(
            $idSuffix
        )->toHtml();
    }

    /**
     * Adding customized price template for product type
     *
     * @param string $type
     * @param string $block
     * @param string $template
     * @return void
     */
    public function addPriceBlockType($type, $block = '', $template = '')
    {
        if ($type) {
            $this->_priceBlockTypes[$type] = array('block' => $block, 'template' => $template);
        }
    }

    /**
     * Get product reviews summary
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     * @return string
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
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Retrieve tier price template
     *
     * @return string
     */
    public function getTierPriceTemplate()
    {
        if (!$this->hasData('tier_price_template')) {
            return $this->_tierPriceDefaultTemplate;
        }

        return $this->getData('tier_price_template');
    }

    /**
     * Returns product tier price block html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getTierPriceHtml($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        return $this->_getPriceBlock(
            $product->getTypeId()
        )->setTemplate(
            $this->getTierPriceTemplate()
        )->setProduct(
            $product
        )->toHtml();
    }

    /**
     * Get tier prices (formatted)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getTierPrices($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $prices = $product->getFormatedTierPrice();

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty'] * 1;

                $_productPrice = $product->getPrice();
                if ($_productPrice != $product->getFinalPrice()) {
                    $_productPrice = $product->getFinalPrice();
                }

                // Group price must be used for percent calculation if it is lower
                $groupPrice = $product->getGroupPrice();
                if ($_productPrice > $groupPrice) {
                    $_productPrice = $groupPrice;
                }

                if ($price['price'] < $_productPrice) {
                    $price['savePercent'] = ceil(100 - 100 / $_productPrice * $price['price']);

                    $tierPrice = $this->_storeManager->getStore()->convertPrice(
                        $this->_taxData->getPrice($product, $price['website_price'])
                    );
                    $price['formated_price'] = $this->_storeManager->getStore()->formatPrice($tierPrice);
                    $price['formated_price_incl_tax'] = $this->_storeManager->getStore()->formatPrice(
                        $this->_storeManager->getStore()->convertPrice(
                            $this->_taxData->getPrice($product, $price['website_price'], true)
                        )
                    );

                    if ($this->_catalogData->canApplyMsrp($product)) {
                        $oldPrice = $product->getFinalPrice();
                        $product->setPriceCalculation(false);
                        $product->setPrice($tierPrice);
                        $product->setFinalPrice($tierPrice);

                        $this->getPriceHtml($product);
                        $product->setPriceCalculation(true);

                        $price['real_price_html'] = $product->getRealPriceHtml();
                        $product->setFinalPrice($oldPrice);
                    }

                    $res[] = $price;
                }
            }
        }

        return $res;
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    protected function _addProductAttributesAndPrices(\Magento\Catalog\Model\Resource\Product\Collection $collection)
    {
        return $collection->addMinimalPrice()->addFinalPrice()->addTaxPercents()->addAttributeToSelect(
            $this->_catalogConfig->getProductAttributes()
        )->addUrlRewrite();
    }

    /**
     * Retrieve given media attribute label or product name if no label
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $mediaAttributeCode
     *
     * @return string
     */
    public function getImageLabel($product = null, $mediaAttributeCode = 'image')
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $label = $product->getData($mediaAttributeCode . '_label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return $label;
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = array())
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

        return (int)$this->_getData('column_count');
    }

    /**
     * Add row size depends on page layout
     *
     * @param string $pageLayout
     * @param int $columnCount
     * @return \Magento\Catalog\Block\Product\ListProduct
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
     * @return \Magento\Object
     */
    public function getPageLayout()
    {
        return $this->_layoutHelper->getCurrentPageLayout();
    }

    /**
     * Check whether the price can be shown for the specified product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function getCanShowProductPrice($product)
    {
        return $product->getCanShowPrice() !== false;
    }

    /**
     * Get if it is necessary to show product stock status
     *
     * @return bool
     */
    public function displayProductStockStatus()
    {
        $statusInfo = new \Magento\Object(array('display_status' => true));
        $this->_eventManager->dispatch('catalog_block_product_status_display', array('status' => $statusInfo));
        return (bool)$statusInfo->getDisplayStatus();
    }

    /**
     * If exists price template block, retrieve price blocks from it
     *
     * @return \Magento\Catalog\Block\Product\AbstractProduct
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /* @var $block \Magento\Catalog\Block\Product\Price\Template */
        $block = $this->getLayout()->getBlock('catalog_product_price_template');
        if ($block) {
            foreach ($block->getPriceBlockTypes() as $type => $priceBlock) {
                $this->addPriceBlockType($type, $priceBlock['block'], $priceBlock['template']);
            }
        }

        return $this;
    }

    /**
     * Product thumbnail image url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getThumbnailUrl($product)
    {
        return (string)$this->_imageHelper->init($product, 'thumbnail')->resize($this->getThumbnailSize());
    }

    /**
     * Thumbnail image size getter
     *
     * @return int
     */
    public function getThumbnailSize()
    {
        return $this->getVar('product_thumbnail_image_size', 'Magento_Catalog');
    }

    /**
     * Product thumbnail image sidebar url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getThumbnailSidebarUrl($product)
    {
        return (string)$this->_imageHelper->init($product, 'thumbnail')->resize($this->getThumbnailSidebarSize());
    }

    /**
     * Thumbnail image sidebar size getter
     *
     * @return int
     */
    public function getThumbnailSidebarSize()
    {
        return $this->getVar('product_thumbnail_image_sidebar_size', 'Magento_Catalog');
    }

    /**
     * Product small image url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getSmallImageUrl($product)
    {
        return (string)$this->_imageHelper->init($product, 'small_image')->resize($this->getSmallImageSize());
    }

    /**
     * Small image size getter
     *
     * @return int
     */
    public function getSmallImageSize()
    {
        return $this->getVar('product_small_image_size', 'Magento_Catalog');
    }

    /**
     * Product small image sidebar url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getSmallImageSidebarUrl($product)
    {
        return (string)$this->_imageHelper->init($product, 'small_image')->resize($this->getSmallImageSidebarSize());
    }

    /**
     * Small image sidebar size getter
     *
     * @return int
     */
    public function getSmallImageSidebarSize()
    {
        return $this->getVar('product_small_image_sidebar_size', 'Magento_Catalog');
    }

    /**
     * Product base image url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getBaseImageUrl($product)
    {
        return (string)$this->_imageHelper->init($product, 'image')->resize($this->getBaseImageSize());
    }

    /**
     * Base image size getter
     *
     * @return int
     */
    public function getBaseImageSize()
    {
        return $this->getVar('product_base_image_size', 'Magento_Catalog');
    }

    /**
     * Product base image icon url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getBaseImageIconUrl($product)
    {
        return (string)$this->_imageHelper->init($product, 'image')->resize($this->getBaseImageIconSize());
    }

    /**
     * Base image icon size getter
     *
     * @return int
     */
    public function getBaseImageIconSize()
    {
        return $this->getVar('product_base_image_icon_size', 'Magento_Catalog');
    }

    /**
     * Get random string
     *
     * @param int $length
     * @param string|null $chars
     * @return string
     */
    public function getRandomString($length, $chars = null)
    {
        return $this->_mathRandom->getRandomString($length, $chars);
    }
}

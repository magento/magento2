<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Quote\Item;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;

/**
 * Shopping cart item render block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Checkout\Block\Cart\Item\Renderer setProductName(string)
 * @method \Magento\Checkout\Block\Cart\Item\Renderer setDeleteUrl(string)
 */
class Renderer extends \Magento\Framework\View\Element\Template implements \Magento\Framework\View\Block\IdentityInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var Item
     */
    protected $_item;

    /**
     * @var string
     */
    protected $_productUrl;

    /**
     * Whether qty will be converted to number
     *
     * @var bool
     */
    protected $_strictQtyMode = true;

    /**
     * Check, whether product URL rendering should be ignored
     *
     * @var bool
     */
    protected $_ignoreProductUrl = false;

    /**
     * Catalog product configuration
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $_productConfig = null;

    /**
     * @var \Magento\Core\Helper\Url
     */
    protected $_urlHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Core\Helper\Url $urlHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Core\Helper\Url $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_imageHelper = $imageHelper;
        $this->_urlHelper = $urlHelper;
        $this->_productConfig = $productConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Set item for render
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    public function setItem(\Magento\Sales\Model\Quote\Item\AbstractItem $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * Get quote item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Get item product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }

    /**
     * Get product thumbnail image
     *
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function getProductThumbnail()
    {
        return $this->_imageHelper->init($this->getProductForThumbnail(), 'thumbnail');
    }

    /**
     * Identify the product from which thumbnail should be taken.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductForThumbnail()
    {
        return $this->getProduct();
    }

    /**
     * Get product thumbnail image url
     *
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function getProductThumbnailUrl()
    {
        return (string)$this->getProductThumbnail()->resize($this->getThumbnailSize());
    }

    /**
     * Product image thumbnail getter
     *
     * @return int
     */
    public function getThumbnailSize()
    {
        return $this->getVar('product_thumbnail_image_size', 'Magento_Catalog');
    }

    /**
     * Get product thumbnail image url for sidebar
     *
     * @return \Magento\Catalog\Model\Product\Image
     */
    public function getProductThumbnailSidebarUrl()
    {
        return (string)$this->getProductThumbnail()->resize(
            $this->getThumbnailSidebarSize()
        )->setWatermarkSize(
            '30x10'
        );
    }

    /**
     * Product image thumbnail getter
     *
     * @return int
     */
    public function getThumbnailSidebarSize()
    {
        return $this->getVar('product_thumbnail_image_sidebar_size', 'Magento_Catalog');
    }

    /**
     * @param string $productUrl
     * @return $this
     */
    public function overrideProductUrl($productUrl)
    {
        $this->_productUrl = $productUrl;
        return $this;
    }

    /**
     * Check Product has URL
     *
     * @return bool
     */
    public function hasProductUrl()
    {
        if ($this->_ignoreProductUrl) {
            return false;
        }

        if ($this->_productUrl || $this->getItem()->getRedirectUrl()) {
            return true;
        }

        $product = $this->getProduct();
        $option = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        if ($product->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($product->hasUrlDataObject()) {
                $data = $product->getUrlDataObject();
                if (in_array($data->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieve URL to item Product
     *
     * @return string
     */
    public function getProductUrl()
    {
        if (!is_null($this->_productUrl)) {
            return $this->_productUrl;
        }
        if ($this->getItem()->getRedirectUrl()) {
            return $this->getItem()->getRedirectUrl();
        }

        $product = $this->getProduct();
        $option = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        return $product->getUrlModel()->getUrl($product);
    }

    /**
     * Get item product name
     *
     * @return string
     */
    public function getProductName()
    {
        if ($this->hasProductName()) {
            return $this->getData('product_name');
        }
        return $this->getProduct()->getName();
    }

    /**
     * Get product customize options
     *
     * @return array
     */
    public function getProductOptions()
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->_productConfig;
        return $helper->getCustomOptions($this->getItem());
    }

    /**
     * Get list of all otions for product
     *
     * @return array
     */
    public function getOptionList()
    {
        return $this->getProductOptions();
    }

    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        return $this->getUrl(
            'checkout/cart/configure',
            ['id' => $this->getItem()->getId(), 'product_id' => $this->getItem()->getProduct()->getId()]
        );
    }

    /**
     * Get quote item qty
     *
     * @return float|int
     */
    public function getQty()
    {
        if (!$this->_strictQtyMode && (string)$this->getItem()->getQty() == '') {
            return '';
        }
        return $this->getItem()->getQty() * 1;
    }

    /**
     * Get checkout session
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Retrieve item messages
     * Return array with keys
     *
     * text => the message text
     * type => type of a message
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = [];
        $quoteItem = $this->getItem();

        // Add basic messages occurring during this page load
        $baseMessages = $quoteItem->getMessage(false);
        if ($baseMessages) {
            foreach ($baseMessages as $message) {
                $messages[] = ['text' => $message, 'type' => $quoteItem->getHasError() ? 'error' : 'notice'];
            }
        }

        /* @var $collection \Magento\Framework\Message\Collection */
        $collection = $this->messageManager->getMessages('quote_item' . $quoteItem->getId());
        if ($collection) {
            $additionalMessages = $collection->getItems();
            foreach ($additionalMessages as $message) {
                /* @var $message \Magento\Framework\Message\MessageInterface */
                $messages[] = ['text' => $message->getText(), 'type' => $message->getType()];
            }
        }
        $this->messageManager->getMessages('quote_item' . $quoteItem->getId())->clear();

        return $messages;
    }

    /**
     * Accept option value and return its formatted view
     *
     * @param string|array $optionValue
     * Method works well with these $optionValue format:
     *      1. String
     *      2. Indexed array e.g. array(val1, val2, ...)
     *      3. Associative array, containing additional option info, including option value, e.g.
     *          array
     *          (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *          )
     *
     * @return array
     */
    public function getFormatedOptionValue($optionValue)
    {
        /* @var $helper \Magento\Catalog\Helper\Product\Configuration */
        $helper = $this->_productConfig;
        $params = [
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>'
        ];
        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * Check whether Product is visible in site
     *
     * @return bool
     */
    public function isProductVisible()
    {
        return $this->getProduct()->isVisibleInSiteVisibility();
    }

    /**
     * Return product additional information block
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    /**
     * Set qty mode to be strict or not
     *
     * @param bool $strict
     * @return $this
     */
    public function setQtyMode($strict)
    {
        $this->_strictQtyMode = $strict;
        return $this;
    }

    /**
     * Set ignore product URL rendering
     *
     * @param bool $ignore
     * @return $this
     */
    public function setIgnoreProductUrl($ignore = true)
    {
        $this->_ignoreProductUrl = $ignore;
        return $this;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getItem()) {
            $identities = $this->getProduct()->getIdentities();
        }
        return $identities;
    }

    /**
     * Get product price formatted with html (final price, special price, mrp price)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPriceHtml(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getPriceRender();
        $priceRender->setItem($this->getItem());

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    /**
     * @return \Magento\Framework\Pricing\Render
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default');
    }

    /**
     * Convert prices for template
     *
     * @param float $amount
     * @param bool $format
     * @return float
     */
    public function convertPrice($amount, $format = false)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat($amount)
            : $this->priceCurrency->convert($amount);
    }

    /**
     * Return the unit price html
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return string
     */
    public function getUnitPriceHtml(\Magento\Sales\Model\Quote\Item\AbstractItem  $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.item.price.unit');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Return row total html
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem  $item
     * @return string
     */
    public function getRowTotalHtml(\Magento\Sales\Model\Quote\Item\AbstractItem  $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.item.price.row');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Return item price html for sidebar
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem  $item
     * @return string
     */
    public function getSidebarItemPriceHtml(\Magento\Sales\Model\Quote\Item\AbstractItem  $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.cart.item.price.sidebar');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Get unit price excluding tax html
     *
     * @param Item\AbstractItem $item
     * @return string
     */
    public function getUnitPriceExclTaxHtml(\Magento\Sales\Model\Quote\Item\AbstractItem  $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.onepage.review.item.price.unit.excl');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Get unit price including tax html
     *
     * @param Item\AbstractItem $item
     * @return string
     */
    public function getUnitPriceInclTaxHtml(\Magento\Sales\Model\Quote\Item\AbstractItem  $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.onepage.review.item.price.unit.incl');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Get row total excluding tax html
     *
     * @param Item\AbstractItem $item
     * @return string
     */
    public function getRowTotalExclTaxHtml(\Magento\Sales\Model\Quote\Item\AbstractItem  $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.onepage.review.item.price.rowtotal.excl');
        $block->setItem($item);
        return $block->toHtml();
    }

    /**
     * Get row total including tax html
     *
     * @param Item\AbstractItem $item
     * @return string
     */
    public function getRowTotalInclTaxHtml(\Magento\Sales\Model\Quote\Item\AbstractItem  $item)
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('checkout.onepage.review.item.price.rowtotal.incl');
        $block->setItem($item);
        return $block->toHtml();
    }
}

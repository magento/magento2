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
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Block\Cart\Item;

use Magento\Sales\Model\Quote\Item;

/**
 * Shopping cart item render block
 *
 * @category    Magento
 * @package     Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @method \Magento\Checkout\Block\Cart\Item\Renderer setProductName(string)
 * @method \Magento\Checkout\Block\Cart\Item\Renderer setDeleteUrl(string)
 */

class Renderer extends \Magento\View\Element\Template
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
     * @var \Magento\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Core\Helper\Url $urlHelper
     * @param \Magento\Message\ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Core\Helper\Url $urlHelper,
        \Magento\Message\ManagerInterface $messageManager,
        array $data = array()
    ) {
        $this->_imageHelper = $imageHelper;
        $this->_urlHelper = $urlHelper;
        $this->_productConfig = $productConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
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
        return (string)$this->getProductThumbnail()
            ->resize($this->getThumbnailSidebarSize())
            ->setWatermarkSize('30x10');
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
        $option  = $this->getItem()->getOptionByCode('product_type');
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
        $option  = $this->getItem()->getOptionByCode('product_type');
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
            array('id' => $this->getItem()->getId())
        );
    }

    /**
     * Get item delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        if ($this->hasDeleteUrl()) {
            return $this->getData('delete_url');
        }

        $encodedUrl = $this->_urlHelper->getEncodedUrl();
        return $this->getUrl(
            'checkout/cart/delete',
            array(
                'id'=>$this->getItem()->getId(),
                \Magento\App\Action\Action::PARAM_NAME_URL_ENCODED => $encodedUrl
            )
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
        $messages = array();
        $quoteItem = $this->getItem();

        // Add basic messages occurring during this page load
        $baseMessages = $quoteItem->getMessage(false);
        if ($baseMessages) {
            foreach ($baseMessages as $message) {
                $messages[] = array(
                    'text' => $message,
                    'type' => $quoteItem->getHasError() ? 'error' : 'notice'
                );
            }
        }

        /* @var $collection \Magento\Message\Collection */
        $collection = $this->messageManager->getMessages('quote_item' . $quoteItem->getId());
        if ($collection) {
            $additionalMessages = $collection->getItems();
            foreach ($additionalMessages as $message) {
                /* @var $message \Magento\Message\MessageInterface */
                $messages[] = array(
                    'text' => $message->getText(),
                    'type' => $message->getType()
                );
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
        $params = array(
            'max_length' => 55,
            'cut_replacer' => ' <a href="#" class="dots" onclick="return false">...</a>'
        );
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
     * @return \Magento\View\Element\AbstractBlock
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    /**
     * Get html for MAP product enabled
     *
     * @param Item $item
     * @return string
     */
    public function getMsrpHtml($item)
    {
        return $this->getLayout()->createBlock('Magento\Catalog\Block\Product\Price')
            ->setTemplate('product/price_msrp_item.phtml')
            ->setProduct($item->getProduct())
            ->toHtml();
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
}

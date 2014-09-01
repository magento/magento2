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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Wishlist\Block;

/**
 * Customer Shared Wishlist Rss Block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Rss extends \Magento\Wishlist\Block\AbstractBlock
{
    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_rss';

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $_wishlistFactory;

    /**
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $_rssFactory;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $_outputHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @param \Magento\Catalog\Helper\Output $outputHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Rss\Model\RssFactory $rssFactory,
        \Magento\Catalog\Helper\Output $outputHelper,
        array $data = array()
    ) {
        $this->_outputHelper = $outputHelper;
        $this->_coreData = $coreData;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_rssFactory = $rssFactory;
        parent::__construct(
            $context,
            $httpContext,
            $productFactory,
            $data
        );
    }

    /**
     * Retrieve Wishlist model
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    protected function _getWishlist()
    {
        if (is_null($this->_wishlist)) {
            $this->_wishlist = parent::_getWishlist();
            $currentCustomerId = $this->_getHelper()->getCustomer()->getId();
            if (!$this->_wishlist->getVisibility() && ($this->_wishlist->getCustomerId() != $currentCustomerId)) {
                $this->_wishlist->unsetData();
            }
        }

        return $this->_wishlist;
    }

    /**
     * Build wishlist rss feed title
     *
     * @return string
     */
    protected function _getTitle()
    {
        return __('%1\'s Wishlist', $this->_getHelper()->getCustomerName());
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        /* @var $rssObj \Magento\Rss\Model\Rss */
        $rssObj = $this->_rssFactory->create();
        if ($this->_getWishlist()->getId()) {
            $newUrl = $this->_urlBuilder->getUrl(
                'wishlist/shared/index',
                array('code' => $this->_getWishlist()->getSharingCode())
            );
            $title = $this->_getTitle();
            $lang = $this->_scopeConfig->getValue(
                'general/locale/code',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $rssObj->_addHeader(
                array(
                    'title' => $title,
                    'description' => $title,
                    'link' => $newUrl,
                    'charset' => 'UTF-8',
                    'language' => $lang
                )
            );

            /** @var $wishlistItem \Magento\Wishlist\Model\Item */
            foreach ($this->getWishlistItems() as $wishlistItem) {
                /* @var $product \Magento\Catalog\Model\Product */
                $product = $wishlistItem->getProduct();
                $productUrl = $this->getProductUrl($product);
                $product->setAllowedInRss(true);
                $product->setAllowedPriceInRss(true);
                $product->setProductUrl($productUrl);
                $args = array('product' => $product);

                $this->_eventManager->dispatch('rss_wishlist_xml_callback', $args);

                if (!$product->getAllowedInRss()) {
                    continue;
                }

                /** @var $outputHelper \Magento\Catalog\Helper\Output */
                $outputHelper = $this->_outputHelper;
                $description = '<table><tr><td><a href="' . $productUrl . '"><img src="' . $this->_imageHelper->init(
                    $product,
                    'thumbnail'
                )->resize(
                    75,
                    75
                ) .
                    '" border="0" align="left" height="75" width="75"></a></td>' .
                    '<td style="text-decoration:none;">' .
                    $outputHelper->productAttribute(
                        $product,
                        $product->getShortDescription(),
                        'short_description'
                    ) . '<p>';

                if ($product->getAllowedPriceInRss()) {
                    $description .= $this->getProductPrice($product);
                }
                $description .= '</p>';

                if ($this->hasDescription($product)) {
                    $description .= '<p>' . __(
                        'Comment:'
                    ) . ' ' . $outputHelper->productAttribute(
                        $product,
                        $product->getDescription(),
                        'description'
                    ) . '<p>';
                }
                $description .= '</td></tr></table>';
                $rssObj->_addEntry(
                    array(
                        'title' => $outputHelper->productAttribute($product, $product->getName(), 'name'),
                        'link' => $productUrl,
                        'description' => $description
                    )
                );
            }
        } else {
            $rssObj->_addHeader(
                array(
                    'title' => __('We cannot retrieve the wish list.'),
                    'description' => __('We cannot retrieve the wish list.'),
                    'link' => $this->_urlBuilder->getUrl(),
                    'charset' => 'UTF-8'
                )
            );
        }

        return $rssObj->createRssXml();
    }

    /**
     * Retrieve Product View URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getProductUrl($product, $additional = array())
    {
        $additional['_rss'] = true;
        return parent::getProductUrl($product, $additional);
    }
}

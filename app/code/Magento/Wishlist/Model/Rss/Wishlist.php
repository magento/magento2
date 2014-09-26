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

namespace Magento\Wishlist\Model\Rss;

use \Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Wishlist RSS model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Wishlist implements DataProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * System event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $outputHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Wishlist\Block\Customer\Wishlist
     */
    protected $wishlistBlock;

    /**
     * @param \Magento\Wishlist\Helper\Rss $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Wishlist $wishlistBlock
     * @param \Magento\Catalog\Helper\Output $outputHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Wishlist\Helper\Rss $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Wishlist $wishlistBlock,
        \Magento\Catalog\Helper\Output $outputHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->wishlistBlock = $wishlistBlock;
        $this->outputHelper = $outputHelper;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->layout = $layout;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function isAllowed()
    {
        return (bool)$this->scopeConfig->getValue(
            'rss/wishlist/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get RSS feed items
     *
     * @return array
     */
    public function getRssData()
    {
        $wishlist = $this->getWishlist();
        if ($wishlist->getId()) {
            $data = $this->getHeader();

            /** @var $wishlistItem \Magento\Wishlist\Model\Item */
            foreach ($wishlist->getItemCollection() as $wishlistItem) {
                /* @var $product \Magento\Catalog\Model\Product */
                $product = $wishlistItem->getProduct();
                $productUrl = $this->wishlistBlock->getProductUrl($product, ['_rss' => true]);
                $product->setAllowedInRss(true);
                $product->setAllowedPriceInRss(true);
                $product->setProductUrl($productUrl);
                $args = array('product' => $product);

                $this->eventManager->dispatch('rss_wishlist_xml_callback', $args);

                if (!$product->getAllowedInRss()) {
                    continue;
                }

                $description = '<table><tr><td><a href="' . $productUrl . '"><img src="'
                    . $this->imageHelper->init($product, 'thumbnail')->resize(75, 75)
                    . '" border="0" align="left" height="75" width="75"></a></td>'
                    . '<td style="text-decoration:none;">'
                    . $this->outputHelper->productAttribute(
                        $product,
                        $product->getShortDescription(),
                        'short_description'
                    ) . '<p>';

                if ($product->getAllowedPriceInRss()) {
                    $description .= $this->getProductPriceHtml($product);
                }
                $description .= '</p>';

                if (trim($product->getDescription()) != '') {
                    $description .= '<p>' . __('Comment:') . ' '
                        . $this->outputHelper->productAttribute(
                            $product,
                            $product->getDescription(),
                            'description'
                        ) . '<p>';
                }
                $description .= '</td></tr></table>';

                $data['entries'][] = (array(
                    'title' => $this->outputHelper->productAttribute($product, $product->getName(), 'name'),
                    'link' => $productUrl,
                    'description' => $description
                ));
            }
        } else {
            $data = array(
                'title' => __('We cannot retrieve the wish list.'),
                'description' => __('We cannot retrieve the wish list.'),
                'link' => $this->urlBuilder->getUrl(),
                'charset' => 'UTF-8'
            );
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return 'rss_wishlist_data';
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return 60;
    }

    /**
     * Get data for Header section of RSS feed
     *
     * @return array
     */
    public function getHeader()
    {
        $title = __('%1\'s Wishlist', $this->wishlistHelper->getCustomerName());
        $newUrl = $this->urlBuilder->getUrl(
            'wishlist/shared/index',
            array('code' => $this->getWishlist()->getSharingCode())
        );

        return array('title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8');
    }

    /**
     * Retrieve Wishlist model
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    protected function getWishlist()
    {
        $wishlist = $this->wishlistHelper->getWishlist();
        $currentCustomer = $this->wishlistHelper->getCustomer();
        if (!$wishlist->getVisibility() && $currentCustomer
            && ($wishlist->getCustomerId() != $currentCustomer->getId())
        ) {
            $wishlist->unsetData();
        }
        return $wishlist;
    }

    /**
     * Return HTML block with product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPriceHtml(\Magento\Catalog\Model\Product $product)
    {
        $price = '';
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->layout->createBlock(
                'Magento\Framework\Pricing\Render',
                'product.price.render.default',
                array('data' => array('price_render_handle' => 'catalog_product_prices'))
            );
        }
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                ['zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST]
            );
        }
        return $price;
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        return array();
    }
}

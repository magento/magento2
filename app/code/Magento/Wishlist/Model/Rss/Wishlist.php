<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Model\Rss;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Render;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Block\Customer\Wishlist as CustomerWishlist;
use Magento\Wishlist\Helper\Rss;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist as ModelWishlist;

/**
 * Wishlist RSS model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Wishlist implements DataProviderInterface
{
    /**
     * Wishlist constructor.
     *
     * @param Rss $wishlistHelper
     * @param CustomerWishlist $wishlistBlock
     * @param Output $outputHelper
     * @param Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $eventManager
     * @param CustomerFactory $customerFactory
     * @param LayoutInterface $layout
     * @param RequestInterface $request
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        protected readonly Rss $wishlistHelper,
        protected readonly CustomerWishlist $wishlistBlock,
        protected readonly Output $outputHelper,
        protected readonly Image $imageHelper,
        protected readonly UrlInterface $urlBuilder,
        protected readonly ScopeConfigInterface $scopeConfig,
        protected readonly ManagerInterface $eventManager,
        protected readonly CustomerFactory $customerFactory,
        protected readonly LayoutInterface $layout,
        protected readonly RequestInterface $request
    ) {
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function isAllowed()
    {
        return $this->scopeConfig->isSetFlag('rss/wishlist/active', ScopeInterface::SCOPE_STORE)
            && $this->getWishlist()->getCustomerId() === $this->wishlistHelper->getCustomer()->getId();
    }

    /**
     * Get RSS feed items
     *
     * @return array
     * @throws LocalizedException
     */
    public function getRssData()
    {
        $wishlist = $this->getWishlist();
        if ($wishlist->getId()) {
            $data = $this->getHeader();

            /** @var $wishlistItem Item */
            foreach ($wishlist->getItemCollection() as $wishlistItem) {
                /* @var $product Product */
                $product = $wishlistItem->getProduct();
                $productUrl = $this->wishlistBlock->getProductUrl($product, ['_rss' => true]);
                $product->setAllowedInRss(true);
                $product->setAllowedPriceInRss(true);
                $product->setProductUrl($productUrl);
                $args = ['product' => $product];

                $this->eventManager->dispatch('rss_wishlist_xml_callback', $args);

                if (!$product->getAllowedInRss()) {
                    continue;
                }

                $description = '<table><tr><td><a href="' . $productUrl . '"><img src="'
                    . $this->imageHelper->init($product, 'rss_thumbnail')->getUrl()
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

                if (is_string($product->getDescription()) && trim($product->getDescription()) !== '') {
                    $description .= '<p>' . __('Comment:') . ' '
                        . $this->outputHelper->productAttribute(
                            $product,
                            $product->getDescription(),
                            'description'
                        ) . '<p>';
                }
                $description .= '</td></tr></table>';

                $data['entries'][] = ([
                    'title' => $product->getName(),
                    'link' => $productUrl,
                    'description' => $description,
                ]);
            }
        } else {
            $data = [
                'title' => __('We cannot retrieve the Wish List.')->render(),
                'description' => __('We cannot retrieve the Wish List.')->render(),
                'link' => $this->urlBuilder->getUrl(),
                'charset' => 'UTF-8',
            ];
        }

        return $data;
    }

    /**
     * GetCacheKey
     *
     * @return string
     */
    public function getCacheKey()
    {
        return 'rss_wishlist_data_' . $this->getWishlist()->getId();
    }

    /**
     * Get Cache Lifetime
     *
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
        $customerId = $this->getWishlist()->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        $title = __('%1\'s Wishlist', $customer->getName())->render();
        $newUrl = $this->urlBuilder->getUrl(
            'wishlist/shared/index',
            ['code' => $this->getWishlist()->getSharingCode()]
        );

        return ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];
    }

    /**
     * Retrieve Wishlist model
     *
     * @return ModelWishlist
     */
    protected function getWishlist()
    {
        $wishlist = $this->wishlistHelper->getWishlist();
        return $wishlist;
    }

    /**
     * Return HTML block with product price
     *
     * @param Product $product
     * @return string
     */
    public function getProductPriceHtml(Product $product)
    {
        $price = '';
        /** @var Render $priceRender */
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->layout->createBlock(
                Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
        if ($priceRender) {
            $price = $priceRender->render(
                'wishlist_configured_price',
                $product,
                ['zone' => Render::ZONE_ITEM_LIST]
            );
        }
        return $price;
    }

    /**
     * @inheritdoc
     */
    public function getFeeds()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function isAuthRequired()
    {
        if ($this->request->getParam('sharing_code') == $this->getWishlist()->getSharingCode()) {
            return false;
        }
        return true;
    }
}

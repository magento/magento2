<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Model\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Wishlist RSS model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Wishlist implements DataProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * System event manager
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager;

    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    protected $layout;

    /**
     * @var \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     * @since 2.0.0
     */
    protected $outputHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    protected $imageHelper;

    /**
     * @var \Magento\Wishlist\Block\Customer\Wishlist
     * @since 2.0.0
     */
    protected $wishlistBlock;

    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     * @since 2.0.0
     */
    protected $customerFactory;

    /**
     * @param \Magento\Wishlist\Helper\Rss $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Wishlist $wishlistBlock
     * @param \Magento\Catalog\Helper\Output $outputHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Wishlist\Helper\Rss $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Wishlist $wishlistBlock,
        \Magento\Catalog\Helper\Output $outputHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->wishlistBlock = $wishlistBlock;
        $this->outputHelper = $outputHelper;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->customerFactory = $customerFactory;
        $this->layout = $layout;
        $this->request = $request;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     * @since 2.0.0
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
     * @since 2.0.0
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

                if (trim($product->getDescription()) != '') {
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
                'title' => __('We cannot retrieve the Wish List.'),
                'description' => __('We cannot retrieve the Wish List.'),
                'link' => $this->urlBuilder->getUrl(),
                'charset' => 'UTF-8',
            ];
        }

        return $data;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCacheKey()
    {
        return 'rss_wishlist_data';
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getCacheLifetime()
    {
        return 60;
    }

    /**
     * Get data for Header section of RSS feed
     *
     * @return array
     * @since 2.0.0
     */
    public function getHeader()
    {
        $customerId = $this->getWishlist()->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        $title = __('%1\'s Wishlist', $customer->getName());
        $newUrl = $this->urlBuilder->getUrl(
            'wishlist/shared/index',
            ['code' => $this->getWishlist()->getSharingCode()]
        );

        return ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];
    }

    /**
     * Retrieve Wishlist model
     *
     * @return \Magento\Wishlist\Model\Wishlist
     * @since 2.0.0
     */
    protected function getWishlist()
    {
        $wishlist = $this->wishlistHelper->getWishlist();
        return $wishlist;
    }

    /**
     * Return HTML block with product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @since 2.0.0
     */
    public function getProductPriceHtml(\Magento\Catalog\Model\Product $product)
    {
        $price = '';
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
        if ($priceRender) {
            $price = $priceRender->render(
                'wishlist_configured_price',
                $product,
                ['zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST]
            );
        }
        return $price;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getFeeds()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isAuthRequired()
    {
        if ($this->request->getParam('sharing_code') == $this->getWishlist()->getSharingCode()) {
            return false;
        }
        return true;
    }
}

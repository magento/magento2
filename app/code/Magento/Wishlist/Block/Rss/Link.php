<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist block customer items
 */
namespace Magento\Wishlist\Block\Rss;

/**
 * @api
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     * @since 2.0.0
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     * @since 2.0.0
     */
    protected $urlEncoder;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->wishlistHelper = $wishlistHelper;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->urlEncoder = $urlEncoder;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getLink()
    {
        return $this->rssUrlBuilder->getUrl($this->getLinkParams());
    }

    /**
     * Check whether status notification is allowed
     *
     * @return bool
     * @since 2.0.0
     */
    public function isRssAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/wishlist/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     * @since 2.0.0
     */
    protected function getLinkParams()
    {
        $params = [];
        $wishlistId = $this->wishlistHelper->getWishlist()->getId();
        $customer = $this->wishlistHelper->getCustomer();
        if ($customer) {
            $key = $customer->getId() . ',' . $customer->getEmail();
            $params = [
                'type' => 'wishlist',
                'data' => $this->urlEncoder->encode($key),
                '_secure' => false
            ];
        }
        if ($wishlistId) {
            $params['wishlist_id'] = $wishlistId;
        }
        return $params;
    }
}

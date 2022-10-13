<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist block customer items
 */
namespace Magento\Wishlist\Block\Rss;

use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Helper\Data;

/**
 * @api
 * @since 100.0.2
 */
class Link extends Template
{
    /**
     * @var Data
     */
    protected Data $wishlistHelper;

    /**
     * @var UrlBuilderInterface
     */
    protected UrlBuilderInterface $rssUrlBuilder;

    /**
     * @var EncoderInterface
     */
    protected EncoderInterface $urlEncoder;

    /**
     * @param Context $context
     * @param Data $wishlistHelper
     * @param UrlBuilderInterface $rssUrlBuilder
     * @param EncoderInterface $urlEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $wishlistHelper,
        UrlBuilderInterface $rssUrlBuilder,
        EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $data['wishlistHelper'] = $wishlistHelper;
        parent::__construct($context, $data);
        $this->wishlistHelper = $wishlistHelper;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->urlEncoder = $urlEncoder;
    }

    /**
     * Return link.
     *
     * @return string
     */
    public function getLink()
    {
        return $this->rssUrlBuilder->getUrl($this->getLinkParams());
    }

    /**
     * Check whether status notification is allowed
     *
     * @return bool
     */
    public function isRssAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/wishlist/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return link params.
     *
     * @return array
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

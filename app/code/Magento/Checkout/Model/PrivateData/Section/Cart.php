<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\PrivateData\Section;

use Magento\Customer\Model\PrivateData\Section\SectionSourceInterface;

/**
 * Cart source
 */
class Cart extends \Magento\Framework\Object implements SectionSourceInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $checkoutCart;

    /**
     * @var \Magento\Catalog\Model\Resource\Url
     */
    protected $catalogUrl;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param array $data
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Checkout\Model\Cart $checkoutCart,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->catalogUrl = $catalogUrl;
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        // TODO: MAGETWO-34824
        return [
            'summary_count' => 5,
            'items' => [1, 2, 3,],
        ];

//        return [
//            'summary_count' => $this->getSummaryCount(),
//            'items' => $this->getRecentItemsData(),
//        ];
    }

    /**
     * Get shopping cart items qty based on configuration (summary qty or items qty)
     *
     * @return int|float
     */
    protected function getSummaryCount()
    {
        if ($this->getData('summary_qty')) {
            return $this->getData('summary_qty');
        }
        return $this->checkoutCart->getSummaryQty();
    }

    /**
     * Get recent items data
     *
     * @return array
     */
    protected function getRecentItemsData()
    {
        $itemsData = [];
        $items = $this->getRecentItems();
        if ($items) {
            foreach ($items as $item) {
                $itemsData = [];
                // TODO: set data
                // TODO: do not miss to check $_cartQty || $block->getAllowCartLink()
            }
        }
        return $itemsData;
    }

    /**
     * Get array of last added items
     *
     * @return array
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        $allItems = array_reverse($this->getItems());
        foreach ($allItems as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $productId = $item->getProduct()->getId();
                $products = $this->catalogUrl->getRewriteByProductStore([$productId => $item->getStoreId()]);
                if (!isset($products[$productId])) {
                    continue;
                }
                $urlDataObject = new \Magento\Framework\Object($products[$productId]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }
            $items[] = $item;
        }
        return $items;
    }

    /**
     * Return customer quote items
     *
     * @return array
     */
    protected function getItems()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote()->getAllVisibleItems();
        }
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
}

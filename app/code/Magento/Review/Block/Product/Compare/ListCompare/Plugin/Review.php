<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Block\Product\Compare\ListCompare\Plugin;

class Review
{
    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory
    ) {
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
    }

    /**
     * Initialize product review
     *
     * @param \Magento\Catalog\Block\Product\Compare\ListCompare $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetReviewsSummaryHtml(
        \Magento\Catalog\Block\Product\Compare\ListCompare $subject,
        \Magento\Catalog\Model\Product $product,
        $templateType = false,
        $displayIfNoReviews = false
    ) {
        if (!$product->getRatingSummary()) {
            $this->reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());
        }
    }
}

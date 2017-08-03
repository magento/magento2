<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Block\Product\Compare\ListCompare\Plugin;

/**
 * Class \Magento\Review\Block\Product\Compare\ListCompare\Plugin\Review
 *
 * @since 2.0.0
 */
class Review
{
    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     * @since 2.0.0
     */
    protected $reviewFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @since 2.0.0
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
     * @since 2.0.0
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

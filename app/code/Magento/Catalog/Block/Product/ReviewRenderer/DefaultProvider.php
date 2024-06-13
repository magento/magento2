<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Block\Product\ReviewRenderer;

use Magento\Catalog\Block\Product\ReviewRendererInterface;

class DefaultProvider implements ReviewRendererInterface
{
    /**
     * Get product review summary html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     * @return string
     */
    public function getReviewsSummaryHtml(
        \Magento\Catalog\Model\Product $product,
        $templateType = self::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        return '';
    }
}

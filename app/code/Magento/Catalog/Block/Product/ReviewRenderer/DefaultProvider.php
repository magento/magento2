<?php
/**
 * Default implementation of product review service provider
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ReviewRenderer;

use Magento\Catalog\Block\Product\ReviewRendererInterface;

/**
 * Class \Magento\Catalog\Block\Product\ReviewRenderer\DefaultProvider
 *
 * @since 2.0.0
 */
class DefaultProvider implements ReviewRendererInterface
{
    /**
     * Get product review summary html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     * @return string
     * @since 2.0.0
     */
    public function getReviewsSummaryHtml(
        \Magento\Catalog\Model\Product $product,
        $templateType = self::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        return '';
    }
}

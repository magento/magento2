<?php
/**
 * Default implementation of product review service provider
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

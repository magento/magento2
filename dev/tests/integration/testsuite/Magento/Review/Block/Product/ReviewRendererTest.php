<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Block\Product;

/**
 * Class ReviewRendererTest
 */
class ReviewRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test verifies ReviewRenderer::getReviewsSummaryHtml call with $displayIfNoReviews = false
     * The reviews summary will be shown as expected only if there is at least one review available
     *
     * @magentoDataFixture Magento/Review/_files/different_reviews.php
     * @magentoAppArea frontend
     */
    public function testGetReviewSummaryHtml()
    {
        $productSku = 'simple';
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get($productSku);
        /** @var  ReviewRenderer $reviewRenderer */
        $reviewRenderer = $objectManager->create(ReviewRenderer::class);
        $actualResult = $reviewRenderer->getReviewsSummaryHtml($product);
        $this->assertEquals(2, $reviewRenderer->getReviewsCount());
        $this->assertStringContainsString('<span itemprop="reviewCount">2</span>', $actualResult);
    }
}

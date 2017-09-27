<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Block\Product;

class ReviewRendererTest extends \PHPUnit\Framework\TestCase
{
    /**
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
        /** @var  \Magento\Review\Block\Product\ReviewRenderer $reviewRenderer */
        $reviewRenderer = $objectManager->create(\Magento\Review\Block\Product\ReviewRenderer::class);
        $actualResult = $reviewRenderer->getReviewsSummaryHtml($product);
        $this->assertEquals(2, $reviewRenderer->getReviewsCount());
        $this->assertNotEmpty($actualResult);
    }
}

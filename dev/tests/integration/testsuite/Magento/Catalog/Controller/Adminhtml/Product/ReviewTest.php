<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * @magentoAppArea adminhtml
 */
class ReviewTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Review/_files/review_xss.php
     */
    public function testEditActionProductNameXss()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get('product-with-xss');

        $reviewId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Review\Model\Review::class
        )->load(
            $product->getId(),
            'entity_pk_value'
        )->getId();
        $this->dispatch('backend/review/product/edit/id/' . $reviewId);
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('&lt;script&gt;alert(&quot;xss&quot;);&lt;/script&gt;', $responseBody);
        $this->assertNotContains('<script>alert("xss");</script>', $responseBody);
    }
}

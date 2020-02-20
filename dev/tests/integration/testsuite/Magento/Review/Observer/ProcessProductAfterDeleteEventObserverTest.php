<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test checks that product review is removed when the corresponding product is removed
 */
class ProcessProductAfterDeleteEventObserverTest extends AbstractController
{
    /**
     * @magentoDataFixture Magento/Review/_files/customer_review.php
     */
    public function testReviewIsRemovedWhenProductDeleted()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        /** @var ReviewCollection $reviewsCollection */
        $reviewsCollection = $objectManager->get(ReviewCollectionFactory::class)->create();
        $reviewsCollection->addEntityFilter('product', $product->getId());

        self::assertEquals(1, $reviewsCollection->count());

        /* Remove product and ensure that the product review is removed as well */
        $productRepository->delete($product);
        $reviewsCollection->clear();

        self::assertEquals(0, $reviewsCollection->count());
    }
}

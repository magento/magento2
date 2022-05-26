<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Block;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Test for displaying product review block.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ViewTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Session */
    private $customerSession;

    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var Registry */
    private $registry;

    /** @var View */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->collectionFactory = $this->objectManager->get(CollectionFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(View::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('current_review');
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * Test product review block
     *
     * @magentoDataFixture Magento/Review/_files/product_review_with_rating.php
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function testProductReviewBlock(): void
    {
        $this->customerSession->setCustomerId(1);
        $review = $this->collectionFactory->create()->addCustomerFilter(1)->getFirstItem();
        $this->registerReview($review);
        $this->assertNotNull($review->getReviewId());

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        /** @var ProductInterface $product */
        $product = $productRepository->get('simple', false, null, true);
        $this->registerProduct($product);

        $blockHtml = $this->block->setReviewId($review->getReviewId())->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//div[contains(@class, 'details')]/h3[contains(text(), '%s')]", $review->getName()),
                $blockHtml
            ),
            'Product name wasn\'t found.'
        );
        $ratings = $this->block->getRating();
        $this->assertCount(2, $ratings);
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//a[contains(@class, 'action back')]/span[contains(text(), '%s')]",
                    __('Back to Product Reviews')
                ),
                $blockHtml
            ),
            sprintf('%s button wasn\'t found.', __('Back to Product Reviews'))
        );
    }

    /**
     * Register the product
     *
     * @param ProductInterface $product
     * @return void
     */
    private function registerProduct(ProductInterface $product): void
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
        $this->registry->register('current_product', $product);
        $this->registry->register('product', $product);
    }

    /**
     * Register the current review
     *
     * @param Product $review
     * @return void
     */
    private function registerReview(Product $review): void
    {
        $this->registry->unregister('current_review');
        $this->registry->register('current_review', $review);
    }
}

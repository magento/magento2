<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Block\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Test for displaying customer product review block.
 *
 * @see \Magento\Review\Block\Customer\View
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
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(View::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Review/_files/customer_review_with_rating.php
     *
     * @return void
     */
    public function testCustomerProductReviewBlock(): void
    {
        $this->customerSession->setCustomerId(1);
        $review = $this->collectionFactory->create()->addCustomerFilter(1)->getFirstItem();
        $this->assertNotNull($review->getReviewId());
        $blockHtml = $this->block->setReviewId($review->getReviewId())->toHtml();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//div[contains(@class, 'product-info')]/h2[contains(text(), '%s')]", $review->getName()),
                $blockHtml
            ),
            'Product name wasn\'t found.'
        );
        $ratings = $this->block->getRating();
        $this->assertCount(2, $ratings);
        foreach ($ratings as $rating) {
            $this->assertEquals(
                1,
                Xpath::getElementsCountForXpath(
                    sprintf(
                        "//div[contains(@class, 'rating-summary')]//span[contains(text(), '%s')]"
                        . "/../..//span[contains(text(), '%s%%')]",
                        $rating->getRatingCode(),
                        $rating->getPercent()
                    ),
                    $blockHtml
                ),
                sprintf('Rating %s was not found or not equals to %s.', $rating->getRatingCode(), $rating->getPercent())
            );
        }
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//div[contains(@class, 'review-title') and contains(text(), '%s')]", $review->getTitle()),
                $blockHtml
            ),
            'Review title wasn\'t found.'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//div[contains(@class, 'review-content') and contains(text(), '%s')]", $review->getDetail()),
                $blockHtml
            ),
            'Review description wasn\'t found.'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//div[contains(@class, 'review-date') and contains(text(), '%s')]/time[contains(text(), '%s')]",
                    __('Submitted on'),
                    $this->block->dateFormat($review->getCreatedAt())
                ),
                $blockHtml
            ),
            'Created date wasn\'t found.'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//a[contains(@href, '/review/customer/')]/span[contains(text(), '%s')]",
                    __('Back to My Reviews')
                ),
                $blockHtml
            ),
            sprintf('%s button wasn\'t found.', __('Back to My Reviews'))
        );
    }
}

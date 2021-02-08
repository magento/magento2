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
 * Test for customer product reviews grid.
 *
 * @see \Magento\Review\Block\Customer\ListCustomer
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 */
class ListCustomerTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Session */
    private $customerSession;

    /** @var ListCustomer */
    private $block;

    /** @var CollectionFactory */
    private $collectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerSession = $this->objectManager->get(Session::class);
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(ListCustomer::class)
            ->setTemplate('Magento_Review::customer/list.phtml');
        $this->collectionFactory = $this->objectManager->get(CollectionFactory::class);
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
    public function testCustomerProductReviewsGrid(): void
    {
        $this->customerSession->setCustomerId(1);
        $review = $this->collectionFactory->create()->addCustomerFilter(1)->addReviewSummary()->getFirstItem();
        $this->assertNotNull($review->getReviewId());
        $blockHtml = $this->block->toHtml();
        $createdDate = $this->block->dateFormat($review->getReviewCreatedAt());
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//td[contains(@class, 'date') and contains(text(), '%s')]", $createdDate),
                $blockHtml
            ),
            sprintf('Created date wasn\'t found or not equals to %s.', $createdDate)
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//td[contains(@class, 'item')]//a[contains(text(), '%s')]", $review->getName()),
                $blockHtml
            ),
            'Product name wasn\'t found.'
        );
        $rating = $review->getSum() / $review->getCount();
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//td[contains(@class, 'summary')]//span[contains(text(), '%s%%')]", $rating),
                $blockHtml
            ),
            sprintf('Rating wasn\'t found or not equals to %s%%.', $rating)
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf("//td[contains(@class, 'description') and contains(text(), '%s')]", $review->getDetail()),
                $blockHtml
            ),
            'Review description wasn\'t found.'
        );
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    "//td[contains(@class, 'actions')]//a[contains(@href, '%s')]/span[contains(text(), '%s')]",
                    $this->block->getReviewUrl($review),
                    __('See Details')
                ),
                $blockHtml
            ),
            sprintf('%s button wasn\'t found.', __('See Details'))
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testCustomerWithoutReviews(): void
    {
        $this->customerSession->setCustomerId(1);
        $this->assertStringContainsString((string)__('You have submitted no reviews.'), strip_tags($this->block->toHtml()));
    }
}

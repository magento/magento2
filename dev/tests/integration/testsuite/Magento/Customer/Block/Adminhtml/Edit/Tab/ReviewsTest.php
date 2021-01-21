<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks customer products reviews tab
 *
 * @see \Magento\Customer\Block\Adminhtml\Edit\Tab\Reviews
 * @magentoAppArea adminhtml
 */
class ReviewsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Reviews */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Reviews::class);
    }

    /**
     * @magentoDataFixture Magento/Review/_files/customer_review.php
     *
     * @return void
     */
    public function testReviewGrid(): void
    {
        $this->block->setCustomerId(1);
        $this->assertCount(1, $this->block->getPreparedCollection());
    }
}

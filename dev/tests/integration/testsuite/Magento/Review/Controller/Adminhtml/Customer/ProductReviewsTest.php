<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Controller\Adminhtml\Customer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for customer product reviews page.
 *
 * @magentoAppArea adminhtml
 */
class ProductReviewsTest extends AbstractBackendController
{
    /** @var LayoutInterface  */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->layout = $this->_objectManager->get(LayoutInterface::class);
    }

    /**
     * Check Customer product review action.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @return void
     */
    public function testProductReviewsAction(): void
    {
        $this->dispatchWithIdParam(1);
        $this->assertStringContainsString('<div id="reviewGrid"', $this->getResponse()->getBody());
    }

    /**
     * @return void
     */
    public function testProductReviews(): void
    {
        $customerId = 1;
        $this->dispatchWithIdParam($customerId);
        $block = $this->layout->getBlock('admin.customer.reviews');
        $this->assertNotFalse($block);
        $this->assertEquals(
            $customerId,
            $block->getCustomerId(),
            'Block customer id value does not match expected value'
        );
    }

    /**
     * Dispatch request with id parameter
     *
     * @param int $id
     * @return void
     */
    private function dispatchWithIdParam(int $id): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams(['id' => $id]);
        $this->dispatch('backend/review/customer/productReviews');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Controller\Adminhtml\Customer;

use Magento\Framework\App\Request\Http;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for customer product reviews page.
 */
class ProductReviewsTest extends AbstractBackendController
{
    /**
     * Check Customer product review action.
     *
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @return void
     */
    public function testProductReviewsAction(): void
    {
        $this->getRequest()->setPostValue(['id' => 1])->setMethod(Http::METHOD_POST);
        $this->dispatch('backend/review/customer/productReviews');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<div id="reviwGrid"', $body);
    }
}

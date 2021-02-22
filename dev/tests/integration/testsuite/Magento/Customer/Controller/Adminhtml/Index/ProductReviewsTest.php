<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Checks customer products reviews controller behaviour.
 *
 * @see \Magento\Customer\Controller\Adminhtml\Index\ProductReviews
 * @magentoAppArea adminhtml
 */
class ProductReviewsTest extends AbstractBackendController
{
    /** @var LayoutInterface */
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
     * @return void
     */
    public function testProductReviews(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParams(['id' => 1]);
        $this->dispatch('backend/customer/index/productReviews');
        $this->assertEquals(
            1,
            $this->layout->getBlock('admin.customer.reviews')->getCustomerId(),
            'Block customer id value does not match expected value'
        );
    }
}

<?php

namespace Magento\Review\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class CustomerTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Base controller URL
     *
     * @var string
     */
    protected $_baseControllerUrl;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->_baseControllerUrl = 'http://localhost/index.php/backend/review/customer/';
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testProductReviewsAction()
    {
        $this->getRequest()->setParam('id', 1);
        $this->dispatch('backend/review/customer/productReviews');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<div id="reviwGrid"', $body);
    }
}

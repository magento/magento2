<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test review product controller behavior
 *
 * @magentoAppArea frontend
 */
class CaseCheckAddingProductReviewTest extends AbstractController
{
    /**
     * Test adding a review for allowed guests with incomplete data by a not logged in user
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Review/_files/config.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testAttemptForGuestToAddReviewsWithIncompleteData()
    {
        $product = $this->getProduct();
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'nickname' => 'Test nick',
            'title' => 'Summary',
            'form_key' => $formKey->getFormKey(),
        ];
        $this->prepareRequestData($post);
        $this->dispatch('review/product/post/id/' . $product->getId());
        $this->assertSessionMessages(
            $this->equalTo(['Please enter a review.']),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test adding a review for not allowed guests by a guest
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Review/_files/disable_config.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testAttemptForGuestToAddReview()
    {
        $product = $this->getProduct();
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'nickname' => 'Test nick',
            'title' => 'Summary',
            'detail' => 'Test Details',
            'form_key' => $formKey->getFormKey(),
        ];

        $this->prepareRequestData($post);
        $this->dispatch('review/product/post/id/' . $product->getId());

        $this->assertRedirect($this->stringContains('customer/account/login'));
    }

    /**
     * Test successfully adding a product review by a guest
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Review/_files/config.php
     * @magentoDataFixture Magento/Catalog/_files/products.php
     */
    public function testSuccessfullyAddingProductReviewForGuest()
    {
        $product = $this->getProduct();
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'nickname' => 'Test nick',
            'title' => 'Summary',
            'detail' => 'Test Details',
            'form_key' => $formKey->getFormKey(),
        ];

        $this->prepareRequestData($post);
        $this->dispatch('review/product/post/id/' . $product->getId());

        $this->assertSessionMessages(
            $this->equalTo(['You submitted your review for moderation.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @return ProductInterface
     */
    private function getProduct()
    {
        return $this->_objectManager->get(ProductRepositoryInterface::class)->get('custom-design-simple-product');
    }

    /**
     * @param array $postData
     * @return void
     */
    private function prepareRequestData($postData)
    {
        $this->getRequest()->setMethod(Request::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
    }
}

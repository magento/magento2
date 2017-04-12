<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class ProductTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testSaveActionWithDangerRequest()
    {
        $this->getRequest()->setPostValue(['product' => ['entity_id' => 15]]);
        $this->dispatch('backend/catalog/product/save');
        $this->assertSessionMessages(
            $this->equalTo(['Unable to save product']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('/backend/catalog/product/new'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndNew()
    {
        $this->getRequest()->setPostValue(['back' => 'new']);
        $repository = $this->_objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $product = $repository->get('simple');
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $this->assertRedirect($this->stringStartsWith('http://localhost/index.php/backend/catalog/product/new/'));
        $this->assertSessionMessages(
            $this->contains('You saved the product.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndDuplicate()
    {
        $this->getRequest()->setPostValue(['back' => 'duplicate']);
        $repository = $this->_objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $product = $repository->get('simple');
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $this->assertRedirect($this->stringStartsWith('http://localhost/index.php/backend/catalog/product/edit/'));
        $this->assertRedirect(
            $this->logicalNot(
                $this->stringStartsWith(
                    'http://localhost/index.php/backend/catalog/product/edit/id/' . $product->getEntityId() . '/'
                )
            )
        );
        $this->assertSessionMessages(
            $this->contains('You saved the product.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $this->assertSessionMessages(
            $this->contains('You duplicated the product.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    public function testIndexAction()
    {
        $this->dispatch('backend/catalog/product');
        $body = $this->getResponse()->getBody();

        $this->assertSelectCount(
            '#add_new_product',
            1,
            $body,
            '"Add Product" button container should be present on Manage Products page, if the limit is not  reached'
        );
        $this->assertSelectCount(
            '#add_new_product-button',
            1,
            $body,
            '"Add Product" button should be present on Manage Products page, if the limit is not reached'
        );
        $this->assertSelectCount(
            '#add_new_product-button.disabled',
            0,
            $body,
            '"Add Product" button should be enabled on Manage Products page, if the limit is not reached'
        );
        $this->assertSelectCount(
            '#add_new_product .action-toggle',
            1,
            $body,
            '"Add Product" button split should be present on Manage Products page, if the limit is not reached'
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testEditAction()
    {
        $repository = $this->_objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
        $product = $repository->get('simple');
        $this->dispatch('backend/catalog/product/edit/id/' . $product->getEntityId());
        $body = $this->getResponse()->getBody();

        $this->assertSelectCount('#save-button', 1, $body, '"Save" button isn\'t present on Edit Product page');
        $this->assertSelectCount(
            '#save_and_new',
            1,
            $body,
            '"Save & New" button isn\'t present on Edit Product page'
        );
        $this->assertSelectCount(
            '#save_and_duplicate',
            1,
            $body,
            '"Save & Duplicate" button isn\'t present on Edit Product page'
        );
    }
}

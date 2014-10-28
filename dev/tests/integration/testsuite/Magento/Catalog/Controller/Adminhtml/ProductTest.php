<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class ProductTest extends \Magento\Backend\Utility\Controller
{
    public function testSaveActionWithDangerRequest()
    {
        $this->getRequest()->setPost(array('product' => array('entity_id' => 15)));
        $this->dispatch('backend/catalog/product/save');
        $this->assertSessionMessages(
            $this->equalTo(array('Unable to save product')),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('/backend/catalog/product/edit'));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndNew()
    {
        $this->getRequest()->setPost(array('back' => 'new'));
        $this->dispatch('backend/catalog/product/save/id/1');
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
        $this->getRequest()->setPost(array('back' => 'duplicate'));
        $this->dispatch('backend/catalog/product/save/id/1');
        $this->assertRedirect($this->stringStartsWith('http://localhost/index.php/backend/catalog/product/edit/'));
        $this->assertRedirect(
            $this->logicalNot($this->stringStartsWith('http://localhost/index.php/backend/catalog/product/edit/id/1/'))
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
        $this->dispatch('backend/catalog/product/edit/id/1');
        $body = $this->getResponse()->getBody();

        $this->assertSelectCount('#save-split-button', 1, $body, '"Save" button isn\'t present on Edit Product page');
        $this->assertSelectCount(
            '#save-split-button-new-button',
            1,
            $body,
            '"Save & New" button isn\'t present on Edit Product page'
        );
        $this->assertSelectCount(
            '#save-split-button-duplicate-button',
            1,
            $body,
            '"Save & Duplicate" button isn\'t present on Edit Product page'
        );
    }
}

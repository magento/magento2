<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        $this->getRequest()->setPostValue(['back' => 'duplicate']);
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

    /**
     * @dataProvider saveWithInvalidCustomOptionDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_without_options.php
     */
    public function testSaveWithInvalidCustomOption($postData)
    {
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product/save/id/1');

        $this->assertSessionMessages(
            $this->contains('You saved the product.'),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Data Provider for save
     *
     * @return array
     */
    public function saveWithInvalidCustomOptionDataProvider()
    {
        return [
            [
                [
                    'product' => [
                        'options' => [
                            [
                                'title' => 'drop_down option',
                                'type' => 'drop_down',
                                'is_require' => true,
                                'sort_order' => 0,
                                'values' => [
                                    [
                                        'title' => 'drop_down option 1',
                                        'price' => 10,
                                        'price_type' => 'fixed',
                                        'sku' => 'drop_down option 1 sku',
                                        'option_type_id' => '-1',
                                        'is_delete' => '',
                                        'sort_order' => 0,
                                    ],
                                    [
                                        'title' => 'drop_down option 2',
                                        'price' => 20,
                                        'price_type' => 'fixed',
                                        'sku' => 'drop_down option 2 sku',
                                        'option_type_id' => '-1',
                                        'is_delete' => '',
                                        'sort_order' => 1,
                                    ],
                                    [
                                        'title' => '',
                                        'price' => '',
                                        'price_type' => 'fixed',
                                        'sku' => '',
                                        'option_type_id' => '-1',
                                        'is_delete' => '1',
                                        'sort_order' => 2,
                                    ]
                                ],
                            ]
                        ],
                    ],
                    'affect_product_custom_options' => 1,
                ]
            ],
        ];
    }
}

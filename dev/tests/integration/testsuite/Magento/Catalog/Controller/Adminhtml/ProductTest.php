<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\Manager;
use Magento\TestFramework\Helper\Bootstrap;

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

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add_new_product"]',
                $body
            ),
            '"Add Product" button container should be present on Manage Products page, if the limit is not  reached'
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add_new_product-button"]',
                $body
            ),
            '"Add Product" button should be present on Manage Products page, if the limit is not reached'
        );
        $this->assertEquals(
            0,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add_new_product-button" and contains(@class,"disabled")]',
                $body
            ),
            '"Add Product" button should be enabled on Manage Products page, if the limit is not reached'
        );
        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="add_new_product"]/*[contains(@class,"action-toggle")]',
                $body
            ),
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

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="save-button"]',
                $body
            ),
            '"Save" button isn\'t present on Edit Product page'
        );

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="save_and_new"]',
                $body
            ),
            '"Save & New" button isn\'t present on Edit Product page'
        );

        $this->assertEquals(
            1,
            \Magento\TestFramework\Helper\Xpath::getElementsCountForXpath(
                '//*[@id="save_and_duplicate"]',
                $body
            ),
            '"Save & Duplicate" button isn\'t present on Edit Product page'
        );
    }

    /**
     * Test create product with already existing url key.
     *
     * @dataProvider saveActionWithAlreadyExistingUrlKeyDataProvider
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     * @param array $postData
     * @return void
     */
    public function testSaveActionWithAlreadyExistingUrlKey(array $postData)
    {
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product/save');
        /** @var Manager $messageManager */
        $messageManager = $this->_objectManager->get(Manager::class);
        $messages = $messageManager->getMessages();
        $errors = $messages->getItemsByType('error');
        $message = array_shift($errors);
        $this->assertSame('URL key for specified store already exists.', $message->getText());
        $this->assertRedirect($this->stringContains('/backend/catalog/product/new'));
        /** @var DataPersistorInterface $dataPersistor */
        $dataPersistor = $this->_objectManager->get(DataPersistorInterface::class);
        $productData = $dataPersistor->get('catalog_product')['product'];
        $image = array_shift($productData['media_gallery']['images']);
        $this->assertStringEndsNotWith('.tmp', $image['file']);
        $this->assertStringEndsNotWith('.tmp', $productData['image']);
        $this->assertStringEndsNotWith('.tmp', $productData['small_image']);
        $this->assertStringEndsNotWith('.tmp', $productData['thumbnail']);
        $this->assertStringEndsNotWith('.tmp', $productData['swatch_image']);
    }

    /**
     * Provide test data for testSaveActionWithAlreadyExistingUrlKey().
     *
     * @return array
     */
    public function saveActionWithAlreadyExistingUrlKeyDataProvider()
    {
        return [
            [
                'post_data' => [
                    'product' =>
                        [
                            'attribute_set_id' => '4',
                            'gift_message_available' => '0',
                            'use_config_gift_message_available' => '1',
                            'links_title' => 'Links',
                            'links_purchased_separately' => '0',
                            'samples_title' => 'Samples',
                            'stock_data' =>
                                [
                                    'min_qty_allowed_in_shopping_cart' =>
                                        [
                                            0 =>
                                                [
                                                    'customer_group_id' => '32000',
                                                    'min_sale_qty' => '',
                                                    'record_id' => '0',
                                                ],
                                        ],
                                    'min_qty' => '0',
                                    'max_sale_qty' => '10000',
                                    'notify_stock_qty' => '1',
                                    'min_sale_qty' => '1',
                                    'qty_increments' => '1',
                                    'use_config_manage_stock' => '1',
                                    'manage_stock' => '1',
                                    'use_config_min_qty' => '1',
                                    'use_config_max_sale_qty' => '1',
                                    'use_config_backorders' => '1',
                                    'backorders' => '0',
                                    'use_config_notify_stock_qty' => '1',
                                    'use_config_enable_qty_inc' => '1',
                                    'enable_qty_increments' => '0',
                                    'use_config_qty_increments' => '1',
                                    'use_config_min_sale_qty' => '1',
                                    'is_qty_decimal' => '0',
                                    'is_decimal_divided' => '0',
                                ],
                            'status' => '1',
                            'affect_product_custom_options' => '1',
                            'name' => 's2',
                            'weight' => '',
                            'url_key' => 'simple-product',
                            'special_price' => '',
                            'cost' => '',
                            'quantity_and_stock_status' =>
                                [
                                    'qty' => '',
                                    'is_in_stock' => '1',
                                ],
                            'website_ids' =>
                                [
                                    1 => '1',
                                ],
                            'sku' => 's2',
                            'meta_title' => 's2',
                            'meta_keyword' => 's2',
                            'meta_description' => 's2 ',
                            'price' => '3',
                            'tax_class_id' => '2',
                            'product_has_weight' => '1',
                            'visibility' => '4',
                            'country_of_manufacture' => '',
                            'page_layout' => '',
                            'options_container' => 'container2',
                            'custom_design' => '',
                            'custom_layout' => '',
                            'news_from_date' => '',
                            'news_to_date' => '',
                            'custom_design_from' => '',
                            'custom_design_to' => '',
                            'special_from_date' => '',
                            'special_to_date' => '',
                            'description' => '',
                            'short_description' => '',
                            'custom_layout_update' => '',
                            'media_gallery' =>
                                [
                                    'images' =>
                                        [
                                            'h17hftqohrd' =>
                                                [
                                                    'position' => '1',
                                                    'media_type' => 'image',
                                                    'video_provider' => '',
                                                    'file' => '/m/a//magento_image.jpg.tmp',
                                                    'value_id' => '',
                                                    'label' => '',
                                                    'disabled' => '0',
                                                    'removed' => '',
                                                    'video_url' => '',
                                                    'video_title' => '',
                                                    'video_description' => '',
                                                    'video_metadata' => '',
                                                    'role' => '',
                                                ],
                                        ],
                                ],
                            'image' => '/m/a//magento_image.jpg.tmp',
                            'small_image' => '/m/a//magento_image.jpg.tmp',
                            'thumbnail' => '/m/a//magento_image.jpg.tmp',
                            'swatch_image' => '/m/a//magento_image.jpg.tmp',
                        ],
                    'is_downloadable' => '0',
                    'affect_configurable_product_attributes' => '1',
                    'new-variations-attribute-set-id' => '4',
                    'configurable-matrix-serialized' => '[]',
                    'associated_product_ids_serialized' => '[]',
                    'form_key' => Bootstrap::getObjectManager()->get(FormKey::class)->getFormKey(),
                ]
            ]
        ];
    }
}

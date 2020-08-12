<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml;

use Magento\Catalog\Model\Product\Attribute\Backend\LayoutUpdate;
use Magento\Framework\Acl\Builder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\CacheCleaner;

/**
 * Test class for Product adminhtml actions
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var Builder
     */
    private $aclBuilder;

    /**
     * @var ProductRepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var ProductResource
     */
    private $resourceModel;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                \Magento\Catalog\Model\Product\Attribute\LayoutUpdateManager::class =>
                    \Magento\TestFramework\Catalog\Model\ProductLayoutUpdateManager::class
            ]
        ]);
        parent::setUp();

        $this->aclBuilder = Bootstrap::getObjectManager()->get(Builder::class);
        $this->repositoryFactory = Bootstrap::getObjectManager()->get(ProductRepositoryFactory::class);
        $this->resourceModel = Bootstrap::getObjectManager()->get(ProductResource::class);
    }

    /**
     * Test calling save with invalid product's ID.
     */
    public function testSaveActionWithDangerRequest()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['product' => ['entity_id' => 15]]);
        $this->dispatch('backend/catalog/product/save');
        $this->assertSessionMessages(
            $this->equalTo(['The product was unable to be saved. Please try again.']),
            MessageInterface::TYPE_ERROR
        );
        $this->assertRedirect($this->stringContains('/backend/catalog/product/new'));
    }

    /**
     * Test saving existing product and specifying that we want redirect to new product form.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndNew()
    {
        $this->getRequest()->setPostValue(['back' => 'new']);
        /** @var ProductRepository $repository */
        $repository = $this->repositoryFactory->create();
        $product = $repository->get('simple');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $this->assertRedirect($this->stringStartsWith('http://localhost/index.php/backend/catalog/product/new/'));
        $this->assertSessionMessages(
            $this->containsEqual('You saved the product.'),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Test saving existing product and specifying that
     * we want redirect to new product form with saved product's data applied.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndDuplicate()
    {
        $this->getRequest()->setPostValue(['back' => 'duplicate']);
        /** @var ProductRepository $repository */
        $repository = $this->repositoryFactory->create();
        $product = $repository->get('simple');
        $this->assertSaveAndDuplicateAction($product);
        $this->assertRedirect($this->stringStartsWith('http://localhost/index.php/backend/catalog/product/edit/'));
        $this->assertRedirect(
            $this->logicalNot(
                $this->stringStartsWith(
                    'http://localhost/index.php/backend/catalog/product/edit/id/' . $product->getEntityId() . '/'
                )
            )
        );
    }

    /**
     * Tests of saving and duplicating existing product after the script execution.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionAndDuplicateWithUrlPathAttribute()
    {
        /** @var ProductRepository $repository */
        $repository = $this->repositoryFactory->create();
        /** @var Product $product */
        $product = $repository->get('simple');

        // set url_path attribute and check it
        $product->setData('url_path', $product->getSku());
        $repository->save($product);
        $urlPathAttribute = $product->getCustomAttribute('url_path');
        $this->assertEquals($urlPathAttribute->getValue(), $product->getSku());

        // clean cache
        CacheCleaner::cleanAll();

        // dispatch Save&Duplicate action and check it
        $this->assertSaveAndDuplicateAction($product);
    }

    /**
     * Testing Add Product button showing.
     */
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
     * Testing existing product edit page.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testEditAction()
    {
        /** @var ProductRepository $repository */
        $repository = $this->repositoryFactory->create();
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
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save');
        /** @var Manager $messageManager */
        $messageManager = $this->_objectManager->get(Manager::class);
        $messages = $messageManager->getMessages();
        $errors = $messages->getItemsByType('error');
        $this->assertNotEmpty($errors);
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
                            'status' => '1',
                            'name' => 's2',
                            'url_key' => 'simple-product',
                            'quantity_and_stock_status' =>
                                [
                                    'qty' => '10',
                                    'is_in_stock' => '1',
                                ],
                            'website_ids' =>
                                [
                                    1 => '1',
                                ],
                            'sku' => 's2',
                            'price' => '3',
                            'tax_class_id' => '2',
                            'product_has_weight' => '0',
                            'visibility' => '4',
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
                                                    'role' => '',
                                                ],
                                        ],
                                ],
                            'image' => '/m/a//magento_image.jpg.tmp',
                            'small_image' => '/m/a//magento_image.jpg.tmp',
                            'thumbnail' => '/m/a//magento_image.jpg.tmp',
                            'swatch_image' => '/m/a//magento_image.jpg.tmp',
                        ],
                ]
            ]
        ];
    }

    /**
     * Test product save with selected tier price
     *
     * @dataProvider saveActionTierPriceDataProvider
     * @param array $postData
     * @param array $tierPrice
     * @magentoDataFixture Magento/Catalog/_files/product_has_tier_price_show_as_low_as.php
     * @magentoConfigFixture current_store catalog/price/scope 1
     */
    public function testSaveActionTierPrice(array $postData, array $tierPrice)
    {
        $postData['product'] = $this->getProductData($tierPrice);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('backend/catalog/product/save/id/' . $postData['id']);
        $this->assertSessionMessages(
            $this->containsEqual('You saved the product.'),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Provide test data for testSaveActionWithAlreadyExistingUrlKey().
     *
     * @return array
     */
    public function saveActionTierPriceDataProvider()
    {
        return [
            [
                'post_data' => [
                    'id' => '1',
                    'type' => 'simple',
                    'store' => '0',
                    'set' => '4',
                    'back' => 'edit',
                    'product' => [],
                    'is_downloadable' => '0',
                    'affect_configurable_product_attributes' => '1',
                    'new_variation_attribute_set_id' => '4',
                    'use_default' => [
                        'gift_message_available' => '0',
                        'gift_wrapping_available' => '0'
                    ],
                    'configurable_matrix_serialized' => '[]',
                    'associated_product_ids_serialized' => '[]'
                ],
                'tier_price_for_request' => [
                    [
                        'price_id' => '1',
                        'website_id' => '0',
                        'cust_group' => '32000',
                        'price' => '111.00',
                        'price_qty' => '100',
                        'website_price' => '111.0000',
                        'initialize' => 'true',
                        'record_id' => '1',
                        'value_type' => 'fixed'
                    ],
                    [
                        'price_id' => '2',
                        'website_id' => '1',
                        'cust_group' => '32000',
                        'price' => '222.00',
                        'price_qty' => '200',
                        'website_price' => '111.0000',
                        'initialize' => 'true',
                        'record_id' => '2',
                        'value_type' => 'fixed'
                    ],
                    [
                        'price_id' => '3',
                        'website_id' => '1',
                        'cust_group' => '32000',
                        'price' => '333.00',
                        'price_qty' => '300',
                        'website_price' => '111.0000',
                        'initialize' => 'true',
                        'record_id' => '3',
                        'value_type' => 'fixed'
                    ]
                ]
            ]
        ];
    }

    /**
     * Return product data for test without entity_id for further save
     *
     * @param array $tierPrice
     * @return array
     */
    private function getProductData(array $tierPrice)
    {
        /** @var ProductRepository $repo */
        $repo = $this->repositoryFactory->create();
        $product = $repo->get('tier_prices')->getData();
        $product['tier_price'] = $tierPrice;
        $product['entity_id'] = null;
        /** @phpstan-ignore-next-line */
        unset($product['entity_id']);
        return $product;
    }

    /**
     * Check whether additional authorization is required for the design fields.
     *
     * @magentoDbIsolation enabled
     * @throws \Throwable
     * @return void
     */
    public function testSaveDesign(): void
    {
        $requestData = [
            'product' => [
                'type' => 'simple',
                'sku' => 'simple',
                'store' => '0',
                'set' => '4',
                'back' => 'edit',
                'type_id' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'product' => [],
                'is_downloadable' => '0',
                'affect_configurable_product_attributes' => '1',
                'new_variation_attribute_set_id' => '4',
                'use_default' => [
                    'gift_message_available' => '0',
                    'gift_wrapping_available' => '0'
                ],
                'configurable_matrix_serialized' => '[]',
                'associated_product_ids_serialized' => '[]'
            ]
        ];
        $uri = 'backend/catalog/product/save';

        //Trying to update product's design settings without proper permissions.
        //Expected list of sessions messages collected throughout the controller calls.
        $sessionMessages = ['Not allowed to edit the product\'s design attributes'];
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_product_design');
        $requestData['product']['custom_design'] = '1';
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch($uri);
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );

        //Trying again with the permissions.
        $this->aclBuilder->getAcl()->allow(null, ['Magento_Catalog::products', 'Magento_Catalog::edit_product_design']);
        $this->getRequest()->setDispatched(false);
        $this->dispatch($uri);
        /** @var ProductRepository $repo */
        $repo = $this->repositoryFactory->create();
        $product = $repo->get('simple');
        $this->assertNotEmpty($product->getCustomDesign());
        $this->assertEquals(1, $product->getCustomDesign());
        //No new error messages
        $this->assertSessionMessages(
            self::equalTo($sessionMessages),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Save design without the permissions but with default values.
     *
     * @magentoDbIsolation enabled
     * @throws \Throwable
     * @return void
     */
    public function testSaveDesignWithDefaults(): void
    {
        $optionsContainerDefault = $this->resourceModel->getAttribute('options_container')->getDefaultValue();
        $requestData = [
            'product' => [
                'type' => 'simple',
                'sku' => 'simple',
                'store' => '0',
                'set' => '4',
                'back' => 'edit',
                'product' => [],
                'type_id' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'is_downloadable' => '0',
                'affect_configurable_product_attributes' => '1',
                'new_variation_attribute_set_id' => '4',
                'use_default' => [
                    'gift_message_available' => '0',
                    'gift_wrapping_available' => '0'
                ],
                'configurable_matrix_serialized' => '[]',
                'associated_product_ids_serialized' => '[]',
                'options_container' => $optionsContainerDefault
            ]
        ];
        $uri = 'backend/catalog/product/save';

        //Updating product's design settings without proper permissions.
        $this->aclBuilder->getAcl()->deny(null, 'Magento_Catalog::edit_product_design');
        //Testing that special "No Update" value is treated as no change.
        $requestData['product']['custom_layout_update_file'] = LayoutUpdate::VALUE_NO_UPDATE;
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->dispatch($uri);

        //Validating saved entity.
        /** @var ProductRepository $repo */
        $repo = $this->repositoryFactory->create();
        $product = $repo->get('simple');
        $this->assertNotNull($product->getData('options_container'));
        $this->assertEquals($optionsContainerDefault, $product->getData('options_container'));
    }

    /**
     * Test custom update files functionality.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     * @throws \Throwable
     * @return void
     */
    public function testSaveCustomLayout(): void
    {
        $file = 'test_file';
        /** @var ProductRepository $repo */
        $repo = $this->repositoryFactory->create();
        $product = $repo->get('simple');
        /** @var ProductLayoutUpdateManager $layoutManager */
        $layoutManager = Bootstrap::getObjectManager()->get(ProductLayoutUpdateManager::class);
        $layoutManager->setFakeFiles((int)$product->getId(), [$file]);
        $productData = $product->getData();
        unset($productData['options']);
        unset($productData[$product->getIdFieldName()]);
        $requestData = [
            'product' => $productData
        ];
        $uri = 'backend/catalog/product/save';

        //Saving a wrong file
        $requestData['product']['custom_layout_update_file'] = $file . 'INVALID';
        $this->getRequest()->setDispatched(false);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setParam('id', $product->getId());
        $this->dispatch($uri);
        $this->assertSessionMessages(
            self::equalTo(['Selected layout update is not available']),
            MessageInterface::TYPE_ERROR
        );

        //Checking that the value is not saved
        /** @var ProductRepository $repo */
        $repo = $this->repositoryFactory->create();
        $product = $repo->get('simple');
        $this->assertEmpty($product->getData('custom_layout_update_file'));

        //Saving the correct file
        $requestData['product']['custom_layout_update_file'] = $file;
        $this->getRequest()->setDispatched(false);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($requestData);
        $this->getRequest()->setParam('id', $product->getId());
        $this->dispatch($uri);

        //Checking that the value is saved
        /** @var ProductRepository $repo */
        $repo = $this->repositoryFactory->create();
        $product = $repo->get('simple');
        $this->assertEquals($file, $product->getData('custom_layout_update_file'));
    }

    /**
     * Dispatch Save&Duplicate action and check it
     *
     * @param Product $product
     */
    private function assertSaveAndDuplicateAction(Product $product)
    {
        $this->getRequest()->setPostValue(['back' => 'duplicate']);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $product->getEntityId());
        $this->assertSessionMessages(
            $this->containsEqual('You saved the product.'),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertSessionMessages(
            $this->containsEqual('You duplicated the product.'),
            MessageInterface::TYPE_SUCCESS
        );
    }
}

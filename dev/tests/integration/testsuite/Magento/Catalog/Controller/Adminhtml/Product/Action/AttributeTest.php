<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action;

use Magento\Backend\Model\Session;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Message\MessageInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends AbstractBackendController
{
    /** @var PublisherConsumerController */
    private $publisherConsumerController;
    /**
     * @var string[]
     */
    private $consumers = ['product_action_attribute.update'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->publisherConsumerController = $this->_objectManager->create(
            PublisherConsumerController::class,
            [
                'consumers' => $this->consumers,
                'logFilePath' => TESTS_TEMP_DIR . "/MessageQueueTestLog.txt",
                'maxMessages' => null,
                'appInitParams' => Bootstrap::getInstance()->getAppInitParams()
            ]
        );

        try {
            $this->publisherConsumerController->startConsumers();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail(
                $e->getMessage()
            );
        }
    }

    protected function tearDown(): void
    {
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveActionRedirectsSuccessfully()
    {
        /** @var $session Session */
        $session = $this->_objectManager->get(Session::class);
        $session->setProductIds([1]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertEquals(302, $this->getResponse()->getHttpResponseCode());
        /** @var \Magento\Backend\Model\UrlInterface $urlBuilder */
        $urlBuilder = $this->_objectManager->get(UrlInterface::class);

        /** @var Attribute $attributeHelper */
        $attributeHelper = $this->_objectManager->get(Attribute::class);
        $expectedUrl = $urlBuilder->getUrl(
            'catalog/product/index',
            ['store' => $attributeHelper->getSelectedStoreId()]
        );
        $isRedirectPresent = false;
        foreach ($this->getResponse()->getHeaders() as $header) {
            if ($header->getFieldName() === 'Location' && strpos($header->getFieldValue(), $expectedUrl) === 0) {
                $isRedirectPresent = true;
            }
        }

        $this->assertTrue($isRedirectPresent);
    }

    /**
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
     *
     * @dataProvider saveActionVisibilityAttrDataProvider
     * @param array $attributes
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveActionChangeVisibility($attributes)
    {
        /** @var ProductRepository $repository */
        $repository = $this->_objectManager->create(ProductRepository::class);
        $product = $repository->get('simple');
        $product->setOrigData();
        $product->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE);
        $product->save();

        /** @var $session Session */
        $session = $this->_objectManager->get(Session::class);
        $session->setProductIds([$product->getId()]);
        $this->getRequest()->setParam('attributes', $attributes);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        /** @var \Magento\Catalog\Model\Category $category */
        $categoryFactory = $this->_objectManager->get(CategoryFactory::class);
        /** @var ListProduct $listProduct */
        $listProduct = $this->_objectManager->get(ListProduct::class);

        sleep(30); // timeout to processing queue
        $this->publisherConsumerController->waitForAsynchronousResult(
            function () use ($repository) {
                sleep(10); // Should be refactored in the scope of MC-22947
                return $repository->get(
                    'simple',
                    false,
                    null,
                    true
                )->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE;
            },
            []
        );

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productItem = $productCollection->getFirstItem();
        $this->assertEquals($session->getProductIds(), [$productItem->getId()]);
    }

    /**
     * @param array $attributes Request parameter.
     *
     * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Validate::execute
     *
     * @dataProvider validateActionDataProvider
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoDbIsolation disabled
     */
    public function testValidateActionWithMassUpdate($attributes)
    {
        /** @var $session Session */
        $session = $this->_objectManager->get(Session::class);
        $session->setProductIds([1, 2]);

        $this->getRequest()->setParam('attributes', $attributes);

        $this->dispatch('backend/catalog/product_action_attribute/validate/store/0');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

        $response = $this->getResponse()->getBody();
        $this->assertJson($response);
        $data = json_decode($response, true);
        $this->assertArrayHasKey('error', $data);
        $this->assertFalse($data['error']);
        $this->assertCount(1, $data);
    }

    /**
     * Data Provider for validation
     *
     * @return array
     */
    public static function validateActionDataProvider()
    {
        return [
            [
                'attributes' => [
                    'name'              => 'Name',
                    'description'       => 'Description',
                    'short_description' => 'Short Description',
                    'price'             => '512',
                    'weight'            => '16',
                    'meta_title'        => 'Meta Title',
                    'meta_keyword'      => 'Meta Keywords',
                    'meta_description'  => 'Meta Description',
                ],
            ]
        ];
    }

    /**
     * Data Provider for save with visibility attribute
     *
     * @return array
     */
    public static function saveActionVisibilityAttrDataProvider()
    {
        return [
            ['attributes' => ['visibility' => Visibility::VISIBILITY_BOTH]],
            ['attributes' => ['visibility' => Visibility::VISIBILITY_IN_CATALOG]]
        ];
    }

    /**
     * Assert that custom layout update can not be change for existing entity.
     *
     * @return void
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveActionCantChangeCustomLayoutUpdate(): void
    {
        /** @var ProductRepository $repository */
        $repository = $this->_objectManager->get(ProductRepository::class);
        $product = $repository->get('simple');

        $product->setOrigData('custom_layout_update', 'test');
        $product->setData('custom_layout_update', 'test');
        $product->save();
        /** @var $session Session */
        $session = $this->_objectManager->get(Session::class);
        $session->setProductIds([$product->getId()]);
        $this->getRequest()->setParam('attributes', ['custom_layout_update' => 'test2']);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertSessionMessages(
            $this->equalTo(['Custom layout update text cannot be changed, only removed']),
            MessageInterface::TYPE_ERROR
        );
        $this->assertEquals('test', $product->getData('custom_layout_update'));
    }
}

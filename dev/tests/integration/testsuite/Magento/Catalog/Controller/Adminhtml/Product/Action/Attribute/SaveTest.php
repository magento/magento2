<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Backend\Model\Session;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\MessageQueue\EnvironmentPreconditionException;
use Magento\TestFramework\MessageQueue\PreconditionFailedException;
use Magento\TestFramework\MessageQueue\PublisherConsumerController;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends AbstractBackendController
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
            ['consumers' => $this->consumers]
        );
        try {
            $this->publisherConsumerController->startConsumers();
        } catch (EnvironmentPreconditionException $e) {
            $this->markTestSkipped($e->getMessage());
        } catch (PreconditionFailedException $e) {
            $this->fail($e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        $this->publisherConsumerController->stopConsumers();
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveActionRedirectsSuccessfully(): void
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
     * @dataProvider saveActionVisibilityAttrDataProvider
     * @param array $attributes
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDbIsolation disabled
     */
    public function testSaveActionChangeVisibility(array $attributes): void
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

        $this->publisherConsumerController->waitForAsynchronousResult(
            fn () => (int) $repository->get('simple', forceReload: true)->getVisibility()
                !== Visibility::VISIBILITY_NOT_VISIBLE
        );

        $category = $categoryFactory->create()->load(2);
        $layer = $listProduct->getLayer();
        $layer->setCurrentCategory($category);
        $productCollection = $layer->getProductCollection();
        $productItem = $productCollection->getFirstItem();
        $this->assertEquals([$product->getId()], [$productItem->getId()]);
        $this->assertEmpty($session->getProductIds());
    }

    /**
     * Data Provider for save with visibility attribute
     *
     * @return array
     */
    public static function saveActionVisibilityAttrDataProvider(): array
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

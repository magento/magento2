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
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;

/**
 * @covers \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save::execute
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends AbstractBackendController
{
    /**
     * Date constants for special price tests
     */
    private const VALID_FROM_DATE = '2026-01-01';
    private const VALID_TO_DATE = '2026-12-31';
    private const INVALID_FROM_DATE = '2026-12-31';
    private const INVALID_TO_DATE = '2026-01-01';
    private const EQUAL_DATE = '2026-06-15';
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
     * @magentoDbIsolation disabled
     */
    #[DataFixture(ProductFixture::class, ['sku' => 'simple'], 'product')]
    public function testSaveActionRedirectsSuccessfully(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        /** @var $session Session */
        $session = $this->_objectManager->get(Session::class);
        $session->setProductIds([$product->getId()]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertSame(302, $this->getResponse()->getHttpResponseCode());
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
     * @param array $attributes
     * @magentoDbIsolation disabled
     */
    #[DataProvider('saveActionVisibilityAttrDataProvider')]
    #[DataFixture(ProductFixture::class, ['sku' => 'simple'], 'product')]
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
            [['visibility' => Visibility::VISIBILITY_BOTH]],
            [['visibility' => Visibility::VISIBILITY_IN_CATALOG]]
        ];
    }

    /**
     * Assert that custom layout update can not be change for existing entity.
     *
     * @return void
     */
    #[DataFixture(ProductFixture::class, ['sku' => 'simple'], 'product')]
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
        $this->assertSame('test', $product->getData('custom_layout_update'));
    }

    /**
     * Test that mass update validates special price dates correctly when from_date is after to_date.
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @return void
     */
    #[DataFixture(ProductFixture::class, ['sku' => 'simple'], 'product')]
    public function testSaveActionValidatesSpecialPriceDateRangeWithInvalidDates(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        /** @var Session $session */
        $session = $this->_objectManager->get(Session::class);
        $session->setProductIds([$product->getId()]);

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue([
            'attributes' => [
                'special_from_date' => self::INVALID_FROM_DATE,
                'special_to_date' => self::INVALID_TO_DATE,
            ],
        ]);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertSessionMessages(
            $this->logicalOr(
                $this->containsEqual('Make sure the To Date is later than or the same as the From Date.'),
                $this->containsEqual('Please correct the product special price dates.')
            ),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test that mass update accepts valid special price dates with various scenarios.
     * Covers:
     * - Both from_date and to_date set with valid range
     * - Only from_date set
     * - Only to_date set
     * - Both dates equal
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @param array $attributes
     * @param float $expectedPrice
     * @param bool $expectFromDate
     * @param bool $expectToDate
     * @return void
     */
    #[DataProvider('validSpecialPriceDateScenariosDataProvider')]
    #[DataFixture(ProductFixture::class, ['sku' => 'simple'], 'product')]
    public function testSaveActionAcceptsValidSpecialPriceDates(
        array $attributes,
        float $expectedPrice,
        bool $expectFromDate,
        bool $expectToDate
    ): void {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        /** @var Session $session */
        $session = $this->_objectManager->get(Session::class);
        $session->setProductIds([$product->getId()]);

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['attributes' => $attributes]);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        // Wait for the specific date field that should be set
        $waitCondition = $expectFromDate
            ? fn () => $productRepository->get('simple', forceReload: true)->getSpecialFromDate() !== null
            : fn () => $productRepository->get('simple', forceReload: true)->getSpecialToDate() !== null;

        $this->publisherConsumerController->waitForAsynchronousResult($waitCondition);

        $this->assertSessionMessages(
            $this->isEmpty(),
            MessageInterface::TYPE_ERROR
        );

        $updatedProduct = $productRepository->get('simple', forceReload: true);
        
        if ($expectFromDate) {
            $this->assertNotNull($updatedProduct->getSpecialFromDate());
        }
        if ($expectToDate) {
            $this->assertNotNull($updatedProduct->getSpecialToDate());
        }
        // Use assertEquals for price comparison as getSpecialPrice() returns string from DB
        $this->assertEquals($expectedPrice, $updatedProduct->getSpecialPrice());
    }

    /**
     * Data provider for valid special price date scenarios.
     *
     * @return array
     */
    public static function validSpecialPriceDateScenariosDataProvider(): array
    {
        return [
            'both dates with valid range' => [
                'attributes' => [
                    'special_from_date' => self::VALID_FROM_DATE,
                    'special_to_date' => self::VALID_TO_DATE,
                    'special_price' => 5.00,
                ],
                'expectedPrice' => 5.00,
                'expectFromDate' => true,
                'expectToDate' => true,
            ],
            'only from_date set' => [
                'attributes' => [
                    'special_from_date' => self::VALID_FROM_DATE,
                    'special_price' => 7.00,
                ],
                'expectedPrice' => 7.00,
                'expectFromDate' => true,
                'expectToDate' => false,
            ],
            'only to_date set' => [
                'attributes' => [
                    'special_to_date' => self::VALID_TO_DATE,
                    'special_price' => 6.00,
                ],
                'expectedPrice' => 6.00,
                'expectFromDate' => false,
                'expectToDate' => true,
            ],
            'both dates equal' => [
                'attributes' => [
                    'special_from_date' => self::EQUAL_DATE,
                    'special_to_date' => self::EQUAL_DATE,
                    'special_price' => 8.00,
                ],
                'expectedPrice' => 8.00,
                'expectFromDate' => true,
                'expectToDate' => true,
            ],
        ];
    }

    /**
     * Test that mass update validates special price dates for multiple products.
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @return void
     */
    #[DataFixture(ProductFixture::class, ['sku' => 'simple'], 'product1')]
    #[DataFixture(ProductFixture::class, ['sku' => 'simple2'], 'product2')]
    public function testSaveActionValidatesSpecialPriceDateRangeForMultipleProducts(): void
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $product1 = $productRepository->get('simple');
        $product2 = $productRepository->get('simple2');

        /** @var Session $session */
        $session = $this->_objectManager->get(Session::class);
        $session->setProductIds([$product1->getId(), $product2->getId()]);

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue([
            'attributes' => [
                'special_from_date' => self::INVALID_FROM_DATE,
                'special_to_date' => self::INVALID_TO_DATE,
            ],
        ]);

        $this->dispatch('backend/catalog/product_action_attribute/save/store/0');

        $this->assertSessionMessages(
            $this->logicalOr(
                $this->containsEqual('Make sure the To Date is later than or the same as the From Date.'),
                $this->containsEqual('Please correct the product special price dates.')
            ),
            MessageInterface::TYPE_ERROR
        );
    }
}
